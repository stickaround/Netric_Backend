<?php

declare(strict_types=1);

namespace Netric\Handler;

use Exception;
use InvalidArgumentException;
use Netric\WorkerMan\WorkerService;
use NetricApi\ErrorException;
use NetricApi\WorkerIf;
use NetricApi\InvalidArgument;

class WorkerHandler implements WorkerIf
{
    /**
     * Service for handling job processing
     *
     * @var WorkerService
     */
    private WorkerService $workerService;

    /**
     * Constructor.
     *
     * @param WorkerService $workerSerivce
     */
    public function __construct(WorkerService $workerSerivce)
    {
        $this->workerService = $workerSerivce;
    }

    /**
     * Process a job
     *
     * @param string $workerName
     * @param string $jsonPayload
     * @return bool
     * @throws ErrorException
     * @throws InvalidArgument
     */
    public function process($workerName, $jsonPayload)
    {
        // Get params for the job
        if (empty($workerName)) {
            throw new InvalidArgument('WorkerName is a required param');
        }

        $payload = json_decode($jsonPayload, true);
        if (!is_array($payload)) {
            throw new InvalidArgument('Payload MUST be valid json');
        }

        // Process job or send exceptions/errors back to calller
        try {
            return $this->workerService->processJob($workerName, $payload);
        } catch (InvalidArgumentException $ex) {
            throw new InvalidArgument($ex->getMessage());
        } catch (Exception $ex) {
            throw new ErrorException($ex->getMessage());
        }
    }
}
