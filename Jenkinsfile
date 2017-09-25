node {
    def dockerImage;
    def clientImage;
    currentBuild.result = "SUCCESS"

    try {
        stage('Build') {
            checkout scm
            docker.withRegistry('https://dockerhub.aereusdev.com', 'aereusdev-dockerhub') {
                /* If this is the master branch, punlish to stable, if it is develop publish to latest */
                if (env.BRANCH_NAME == 'master') {
                    clientImage = docker.image("netric-client-web:stable")
                } else {
                    clientImage = docker.image("netric-client-web:latest")
                }

                clientImage.pull()
            }

            /* Get the built client from netric.client.web container and copy to the local mounted server/mobile directory */
            clientImage.inside {
                sh 'cp -r /var/www/app/build/* ./server/mobile/'
            }

            dockerImage = docker.build('netric');
        }

        stage('Test') {
            sh 'docker-compose -f docker/docker-compose-test.yml down'
            sh 'docker-compose -f docker/docker-compose-test.yml up -d'
            /* TODO: figure out pause before running setup here */
            sh 'docker exec docker_netric_server_1 /netric-setup.sh'
            sh 'docker exec docker_netric_server_1 /netric-tests.sh'
            sh 'docker-compose -f docker/docker-compose-test.yml down'
            junit 'server/tests/tmp/logfile.xml'
        }

        stage('Publish') {
            dockerImage = docker.build('netric')
            docker.withRegistry('https://dockerhub.aereusdev.com', 'aereusdev-dockerhub') {
                /* If this is the master branch, punlish to stable, if it is develop publish to latest */
                if (env.BRANCH_NAME == 'master') {
                    dockerImage.push("stable")
                } else {
                    dockerImage.push("latest")
                }
            }
        }

        stage('Deploy') {
            sshagent (credentials: ['aereus']) {
                sh 'ssh aereus@web1.aereus.com uname -a'
                
                /*
                sh 'docker login -u aereusdev -p p7pfsGRe dockerhub.aereusdev.com'
                sh 'docker pull dockerhub.aereusdev.com/netric:latest'
                sh 'docker rename netric netric_dep'
                sh 'docker run -d -p 80 --restart=unless-stopped --name netric -e APPLICATION_ENV="production" -e VIRTUAL_HOST=aereus.netric.com -e LETSENCRYPT_HOST=aereus.netric.com -e LETSENCRYPT_EMAIL=sky.stebnicki@netric.com dockerhub.aereusdev.com/netric:latest'
                sh 'docker stop netric_dep'
                sh 'docker rm netric_dep'
                */
            }
        }

        stage('Cleanup') {
            echo 'prune and cleanup'
            sh 'docker system prune -f'

            mail body: 'project build successful: ${env.BUILD_URL}',
                from: 'builds@aereus.com',
                subject: 'project build successful',
                to: 'sky.stebnicki@aereus.com'
        }

    } catch (err) {
        currentBuild.result = "FAILURE"
        mail body: "project build error is here: ${env.BUILD_URL}" ,
        subject: 'project build failed',
        to: 'sky.stebnicki@aereus.com'
        throw err
    }
}
