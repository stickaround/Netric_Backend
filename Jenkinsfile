node {
    def dockerImage;
    def clientImage;
    currentBuild.result = "SUCCESS"

    try {
        stage('Build') {
            deleteDir()
            checkout scm
            docker.withRegistry('https://dockerhub.aereusdev.com', 'aereusdev-dockerhub') {
                /* If this is the master branch, punlish to stable, if it is develop publish to latest */
                if (env.BRANCH_NAME == 'master') {
                    clientImage = docker.image("dockerhub.aereusdev.com/netric-client-web:stable")
                } else {
                    clientImage = docker.image("dockerhub.aereusdev.com/netric-client-web:latest")
                }

                clientImage.pull()
            }

            /* Get the built client from netric.client.web container and copy to the local mounted server/mobile directory */
            clientImage.inside {
                sh 'cp -r /var/www/app/build/* ./public/mobile/'
            }

            dockerImage = docker.build('netric');
        }

        stage('Test') {
            sh 'docker-compose -f docker/docker-compose-test.yml pull'
            sh 'docker-compose -f docker/docker-compose-test.yml build'
            sh 'docker-compose -f docker/docker-compose-test.yml up -d'
            // Give mogilefs time to settle
            sleep 10
            sh 'docker exec docker_netric_server_1 /netric-setup.sh'
            sh 'docker exec docker_netric_server_1 /netric-tests.sh'
            sh 'docker-compose -f docker/docker-compose-test.yml down'
            junit 'tests/tmp/logfile.xml'
        }

        stage('Publish') {
            dockerImage = docker.build('netric')
            docker.withRegistry('https://dockerhub.aereusdev.com', 'aereusdev-dockerhub') {
                /* If this is the master branch, publish to stable, if it is develop publish to latest */
                if (env.BRANCH_NAME == 'master') {
                    dockerImage.push("stable")
                } else {
                    dockerImage.push("latest")
                }
            }
        }

        stage('Deploy') {
            sshagent (credentials: ['aereus']) {
                /*
                  * We no longer need to deploy because the watcher in production will automatically
                  * pull updates from dockerhub.aereusdev.com
                sh 'scp -o StrictHostKeyChecking=no scripts/pull-and-run.sh aereus@web1.aereus.com:/home/aereus/pull-and-run.sh'
                sh 'ssh -o StrictHostKeyChecking=no aereus@web1.aereus.com chmod +x /home/aereus/pull-and-run.sh'
                sh 'ssh -o StrictHostKeyChecking=no aereus@web1.aereus.com /home/aereus/pull-and-run.sh latest'
                sh 'ssh -o StrictHostKeyChecking=no aereus@web1.aereus.com rm /home/aereus/pull-and-run.sh'
                */
                sh 'scp -o StrictHostKeyChecking=no scripts/pull-and-run-daemon.sh aereus@db2.aereus.com:/home/aereus/pull-and-run-daemon.sh'
                sh 'ssh -o StrictHostKeyChecking=no aereus@db2.aereus.com chmod +x /home/aereus/pull-and-run-daemon.sh'
                sh 'ssh -o StrictHostKeyChecking=no aereus@db2.aereus.com /home/aereus/pull-and-run-daemon.sh latest'
                sh 'ssh -o StrictHostKeyChecking=no aereus@db2.aereus.com rm /home/aereus/pull-and-run-daemon.sh'
            }
        }

        stage('Cleanup') {
            sh 'docker system prune -f'
        }

    } catch (err) {
        //sh 'docker system prune -f'

        currentBuild.result = "FAILURE"
        mail body: "project build error is here: ${env.BUILD_URL}" ,
        subject: 'project build failed',
        to: 'sky.stebnicki@aereus.com'
        throw err
    }
}
