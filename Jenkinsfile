node {
    def dockerImage;
    def clientImage;
    currentBuild.result = "SUCCESS"

    try {
        stage('Build') {
            checkout scm
            docker.withRegistry('https://dockerhub.aereusdev.com', 'aereusdev-dockerhub') {
                clientImage = docker.image("https://dockerhub.aereusdev.com/netric-client-web")
                
                /* If this is the master branch, punlish to stable, if it is develop publish to latest *
                if (env.BRANCH_NAME == 'develop') {
                    clientImage = docker.image("https://dockerhub.aereusdev.com/netric-client-web:latest")
                } else {
                    clientImage = docker.image("https://dockerhub.aereusdev.com/netric-client-web:stable")
                }*/

                clientImage.pull()
            }

            /* Get the built client from netric.client.web container and copy to the local mounted server/mobile directory */
            clientImage.inside {
                sh 'cp -r /var/ww/app/build/* ./server/mobile/'
            }

            dockerImage.build('netric');
        }

        stage('Test') {
           sh 'docker-compose -f docker/docker-compose-test.yml up -d'
           /* TODO: figure out pause before running setup here */
           sh 'docker exec docker_netric_server_1 /netric-setup.sh'
           sh 'docker exec docker_netric_server_1 /netric-tests.sh'
           junit 'server/tests/tmp/logfile.xml'
        }

        stage('Publish') {
            dockerImage = docker.build('netric')
            docker.withRegistry('https://dockerhub.aereusdev.com', 'aereusdev-dockerhub') {
                /* If this is the master branch, punlish to stable, if it is develop publish to latest 
                if (env.BRANCH_NAME == 'develop') {
                    dockerImage.push("latest")
                } else {
                    dockerImage.push("stable")
                }*/

                dockerImage.push("latest")
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
