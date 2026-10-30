pipeline {
    agent any

    options {
        timestamps()
        disableConcurrentBuilds()
    }

    environment {
        AWS_REGION = 'us-east-1'
        ECR_REGISTRY = '148676267942.dkr.ecr.us-east-1.amazonaws.com'
        ECR_REPOSITORY = 'act_hw2_iqa_system'
        SWARM_MANAGER = '172.31.19.179'
        STACK_NAME = 'iqa'
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Validate Project Files') {
            steps {
                sh '''
                    test -f Dockerfile
                    test -f docker-compose.yml
                    test -f database/init.sql
                    test -f docker-stack.yml
                '''
                echo 'Required project files are present.'
            }
        }

        stage('Build Docker Image') {
            steps {
                sh '''
                    docker build \
                      -t ${ECR_REGISTRY}/${ECR_REPOSITORY}:${BUILD_NUMBER} \
                      -t ${ECR_REGISTRY}/${ECR_REPOSITORY}:latest \
                      .
                '''
            }
        }

        stage('Login to Amazon ECR') {
            steps {
                sh '''
                    aws ecr get-login-password --region ${AWS_REGION} \
                      | docker login --username AWS --password-stdin ${ECR_REGISTRY}
                '''
            }
        }

        stage('Push Image to Amazon ECR') {
            steps {
                sh '''
                    docker push ${ECR_REGISTRY}/${ECR_REPOSITORY}:${BUILD_NUMBER}
                    docker push ${ECR_REGISTRY}/${ECR_REPOSITORY}:latest
                '''
            }
        }


        stage('Deploy to Docker Swarm') {
            steps {
                withCredentials([string(
                    credentialsId: 'iqa-db-root-password',
                    variable: 'DB_SECRET'
                )]) {
                    sh '''#!/bin/bash
                        set +x
                        set -euo pipefail

                        if ! ssh -i /var/lib/jenkins/.ssh/id_ed25519 \
                            -o BatchMode=yes \
                            ubuntu@${SWARM_MANAGER} \
                            'sudo docker secret inspect iqa_db_root_password >/dev/null 2>&1'
                        then
                            printf '%s' "$DB_SECRET" | ssh \
                                -i /var/lib/jenkins/.ssh/id_ed25519 \
                                -o BatchMode=yes \
                                ubuntu@${SWARM_MANAGER} \
                                'sudo docker secret create iqa_db_root_password - >/dev/null'
                        fi
                    '''
                }

                sh '''#!/bin/bash
                    set -euo pipefail

                    sed "s#${ECR_REGISTRY}/${ECR_REPOSITORY}:latest#${ECR_REGISTRY}/${ECR_REPOSITORY}:${BUILD_NUMBER}#g" \
                        docker-stack.yml > docker-stack-deploy.yml

                    scp -i /var/lib/jenkins/.ssh/id_ed25519 \
                        docker-stack-deploy.yml \
                        ubuntu@${SWARM_MANAGER}:/tmp/docker-stack.yml

                    scp -i /var/lib/jenkins/.ssh/id_ed25519 \
                        database/init.sql \
                        ubuntu@${SWARM_MANAGER}:/tmp/init.sql

                    ssh -i /var/lib/jenkins/.ssh/id_ed25519 \
                        ubuntu@${SWARM_MANAGER} \
                        'mkdir -p /home/ubuntu/iqa-swarm/database &&
                        mv /tmp/docker-stack.yml /home/ubuntu/iqa-swarm/docker-stack.yml &&
                        mv /tmp/init.sql /home/ubuntu/iqa-swarm/database/init.sql'
                '''

                sh '''#!/bin/bash
                    set -euo pipefail

                    ssh -i /var/lib/jenkins/.ssh/id_ed25519 \
                        ubuntu@${SWARM_MANAGER} \
                        'aws ecr get-login-password --region us-east-1 |
                        sudo docker login --username AWS --password-stdin 148676267942.dkr.ecr.us-east-1.amazonaws.com'

                    ssh -i /var/lib/jenkins/.ssh/id_ed25519 \
                        ubuntu@${SWARM_MANAGER} \
                        'sudo docker stack deploy --with-registry-auth -c /home/ubuntu/iqa-swarm/docker-stack.yml iqa'
                '''
            }
        }

    }

    post {
        success {
            echo 'CI/CD pipeline completed successfully.'
        }
        failure {
            echo 'Pipeline failed. Review the stage logs.'
        }
    }
}
