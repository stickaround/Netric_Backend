<?php
// This is no longer necessary but we'll leave the file to skip over the version

/**
 * Reset password for sky in the aereus account after all passwords getting
 * blown out with a previous bug.
 *
 * We only need to do this once for aereus because no other accounts were
 * or will be impacted by this bug.
 */
//use Netric\Entity\EntityLoaderFactory;
//use Netric\EntityQuery;
//
//$account = $this->getAccount();
//$serviceManager = $account->getServiceManager();
//$entityLoader = $serviceManager->get(EntityLoaderFactory::class);
//
//// Production aereus
//if ($account->getName() == 'aereus') {
//    // Get sky's user
//    $sky = $account->getUser(null, 'sky.stebnicki');
//    // Set to temp password (this will be changed as soon as the script loads)
//    $sky->setValue('password', 'K76IufpHbpm7nmI4');
//    $entityLoader->save($sky);
//}
//
//// Fix test account in integ
//if ($account->getName() == 'integ') {
//    // Get the test user
//    $testUser = $account->getUser(null, 'test@netric.com');
//    if ($testUser) {
//        $testUser->setValue('password', 'password');
//        $entityLoader->save($testUser);
//    }
//}
