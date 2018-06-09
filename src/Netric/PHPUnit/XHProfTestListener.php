<?php
namespace Netric\PHPUnit;

use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Framework\AssertionFailedError;

/**
 * A TestListener that integrates with XHProf.
 *
 * Here is an example XML configuration for activating this listener:
 *
 * <code>
 * <listeners>
 *  <listener class="Netric\PHPUnit\XHProfTestListener">
 *   <arguments>
 *    <array>
 *     <element key="xhprofRunsFolder">
 *      <string>/var/www/html/data/profile_runs</string>
 *     </element>
 *     <element key="appNamespace">
 *      <string>netric</string>
 *     </element>
 *     <element key="xhprofFlags">
 *      <string>XHPROF_FLAGS_CPU,XHPROF_FLAGS_MEMORY</string>
 *     </element>
 *     <element key="xhprofIgnore">
 *      <string>call_user_func,call_user_func_array</string>
 *     </element>
 *    </array>
 *   </arguments>
 *  </listener>
 * </listeners>
 * </code>
 */
class XHProfTestListener implements TestListener
{
    /**
     * @var array
     */
    protected $runs = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * Internal tracking for test suites.
     *
     * Increments as more suites are run, then decremented as they finish. All
     * suites have been run when returns to 0.
     *
     * @var integer
     */
    protected $suites = 0;

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if (!isset($options['appNamespace'])) {
            throw new InvalidArgumentException(
                'The "appNamespace" option is not set.'
            );
        }
        $this->options = $options;
    }

    /**
     * An error occurred.
     *
     * @param Test       $test
     * @param \Exception $e
     * @param float      $time
     */
    public function addError(Test $test, \Throwable $t, float $time): void
    {
    }

    /**
     * A warning occurred.
     *
     * @param Test    $test
     * @param Warning $e
     * @param float   $time
     */
    public function addWarning(Test $test, Warning $e, float $time): void
    {
    }

    /**
     * A failure occurred.
     *
     * @param Test                 $test
     * @param AssertionFailedError $e
     * @param float                $time
     */
    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
    }

    /**
     * Incomplete test.
     *
     * @param Test       $test
     * @param \Exception $e
     * @param float      $time
     */
    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
    {
    }

    /**
     * Risky test.
     *
     * @param Test       $test
     * @param \Exception $e
     * @param float      $time
     */
    public function addRiskyTest(Test $test, \Throwable $t, float $time): void
    {
    }

    /**
     * Skipped test.
     *
     * @param Test       $test
     * @param \Exception $e
     * @param float      $time
     */
    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
    {
    }

    /**
     * A test started.
     *
     * @param Test $test
     */
    public function startTest(Test $test): void
    {
        if (!extension_loaded('xhprof')) {
            return;
        }

        if (!isset($this->options['xhprofFlags'])) {
            $flags = XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY;
        } else {
            $flags = 0;
            foreach (explode(',', $this->options['xhprofFlags']) as $flag) {
                $flags += constant($flag);
            }
        }
        xhprof_enable($flags, array(
            'ignored_functions' => explode(',', $this->options['xhprofIgnore'])
        ));
    }

    /**
     * A test ended.
     *
     * @param Test  $test
     * @param float $time
     */
    public function endTest(Test $test, float $time): void
    {
        if (!extension_loaded('xhprof')) {
            return;
        }

        // Check if we are only profiling tests slower than the threshold
        if ($this->options['slowThreshold']) {
            $timeInMs = $this->toMilliseconds($time);
            if ($timeInMs < (int)$this->options['slowThreshold']) {
                // This test was fast enough, do nothing
                return;
            }
        }

        $xhprofData = xhprof_disable();
        $runId = uniqid();
        $fileName = $this->options['xhprofRunsFolder'] . '/' . $runId . '.' . $this->options['appNamespace'] . '.xhprof';
        $file = fopen($fileName, 'w');
        if ($file) {
            // Use PHP serialize function to store the XHProf's
            fwrite($file, serialize($xhprofData));
            fclose($file);
        }

        /*
        $runs = new \XHProfRuns_Default;
        $run = $runs->save_run($data, $this->options['appNamespace']);
         */

        $test_name = get_class($test) . '::' . $test->getName();

        $this->runs[$test_name] = [
            'timeinms' => $this->toMilliseconds($time),
            'file' => $this->options['xhprofWeb'] . '?run=' . $runId . '&source=' . $this->options['appNamespace']
        ];
    }

    /**
     * A test suite started.
     *
     * @param TestSuite $suite
     */
    public function startTestSuite(TestSuite $suite): void
    {
        $this->suites++;
    }

    /**
     * A test suite ended.
     *
     * @param TestSuite $suite
     */
    public function endTestSuite(TestSuite $suite): void
    {
        if (!extension_loaded('xhprof')) {
            return;
        }

        $this->suites--;
        if ($this->suites == 0 && count($this->runs) > 0) {
            print("\n\nXHProf runs for tests exceeding threshold: ");
            print(count($this->runs) . "\n");
            foreach ($this->runs as $test => $run) {
                print(' * ' . $test . " - " . $run['timeinms'] . "ms\n   " . $run['file'] . "\n\n");
            }
            print("\n");
        }
    }

    /**
     * Convert PHPUnit's reported test time (microseconds) to milliseconds.
     */
    private function toMilliseconds(float $time) : int
    {
        return (int)round($time * 1000);
    }
}
