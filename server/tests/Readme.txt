We follow strict unit testing standards at Aereus. This document describes how to run unit tests.

// PHP
// ----------------------------------------------------------------

// To run all from the <project_root>/tests directory type:
phpunit -c phpunit.xml

// JS - uses google's js-test-driver server
// ----------------------------------------------------------------

// First start server from the <project_root>/tests directory type:
jsunitserver.bat

// Then open a browser and go to http://localhost:9876/capture

// To run all tests from the <project_root>/tests/js directory type:
jsunit.bat
