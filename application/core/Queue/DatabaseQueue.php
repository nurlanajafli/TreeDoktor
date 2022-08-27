<?php

namespace application\core\Queue;

use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Database\Connection;
use Illuminate\Queue\Queue;
use Illuminate\Support\Str;
use application\core\Queue\Jobs\DatabaseJob;
use Illuminate\Queue\Jobs\DatabaseJobRecord;

class DatabaseQueue extends Queue implements QueueContract
{
    /**
     * The database connection instance.
     *
     * @var \Illuminate\Database\Connection
     */
    protected $database;

    /**
     * The database table that holds the jobs.
     *
     * @var string
     */
    protected $table;

    /**
     * The name of the default queue.
     *
     * @var string
     */
    protected $default;

    /**
     * The expiration time of a job.
     *
     * @var int|null
     */
    protected $retryAfter = 60;

    /**
     * Create a new database queue instance.
     *
     * @param  \Illuminate\Database\Connection  $database
     * @param  string  $table
     * @param  string  $default
     * @param  int  $retryAfter
     * @return void
     */
    public function __construct(Connection $database, $table, $default = 'default', $retryAfter = 60)
    {
        $this->table = $table;
        $this->default = $default;
        $this->database = $database;
        $this->retryAfter = $retryAfter;
    }

    /**
     * Get the size of the queue.
     *
     * @param  string|null  $queue
     * @return int
     */
    public function size($queue = null)
    {
        return $this->database->table($this->table)
                    ->where('queue', $this->getQueue($queue))
                    ->count();
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  string  $job
     * @param  mixed  $data
     * @param  string|null  $queue
     * @return mixed
     */
    public function push($job, $data = '', $queue = null)
    {
        return $this->pushToDatabase($queue, $this->createPayload(
            $job, $this->getQueue($queue), $data
        ));
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param  string  $payload
     * @param  string|null  $queue
     * @param  array  $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        /**
         * Not used with CI jobs
         */
//        return $this->pushToDatabase($queue, $payload);

        return null;
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @param  string  $job
     * @param  mixed  $data
     * @param  string|null  $queue
     * @return void
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        return $this->pushToDatabase($queue, $this->createPayload(
            $job, $this->getQueue($queue), $data
        ), $delay);
    }

    /**
     * Push an array of jobs onto the queue.
     *
     * @param  array  $jobs
     * @param  mixed  $data
     * @param  string|null  $queue
     * @return mixed
     */
    public function bulk($jobs, $data = '', $queue = null)
    {
        $queue = $this->getQueue($queue);

        $availableAt = $this->availableAt();

        return $this->database->table($this->table)->insert(collect((array) $jobs)->map(
            function ($job) use ($queue, $data, $availableAt) {
                return $this->buildDatabaseRecord($queue, $this->createPayload($job, $this->getQueue($queue), $data), $availableAt);
            }
        )->all());
    }

    /**
     * Release a reserved job back onto the queue.
     *
     * @param  string  $queue
     * @param  \Illuminate\Queue\Jobs\DatabaseJobRecord  $job
     * @param  int  $delay
     * @return mixed
     */
    public function release($queue, $job, $delay)
    {
        return $this->pushToDatabase($queue, $job->payload, $delay, $job->attempts);
    }

    /**
     * Push a raw payload to the database with a given delay.
     *
     * @param  string|null  $queue
     * @param  string  $payload
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @param  int  $attempts
     * @return mixed
     */
    protected function pushToDatabase($queue, $payload, $delay = 0, $attempts = 0)
    {
        return $this->database->table($this->table)->insertGetId($this->buildDatabaseRecord(
            $this->getQueue($queue), $payload, $this->availableAt($delay), $attempts
        ));
    }

    /**
     * Create an array to insert for the given job.
     *
     * @param  string|null  $queue
     * @param  string  $payload
     * @param  int  $availableAt
     * @param  int  $attempts
     * @return array
     */
    protected function buildDatabaseRecord($queue, $payload, $availableAt, $attempts = 0)
    {
        $className = '';
        $data = json_decode($payload, true);

        if (sizeof($data)) {
            $className = $data['class_name'];
            unset($data['class_name']);
            $payload = json_encode($data);
        }

        return [
            'job_driver' => $className,
            'job_payload' => $payload,
            'job_attempts' => 0,
            'job_is_completed' => 0,
            'job_available_at' => $availableAt,
            'job_reserved_at' => 0,
            'job_created_at' => date('Y-m-d H:i:s'),
        ];
    }

    public function getQueueJob($job) {
        $dbJob = new DatabaseJobRecord((object) $job);

        return new DatabaseJob(
            $this->container, $this, $dbJob, $this->connectionName, 'default'
        );
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param  string|null  $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     *
     * @throws \Throwable
     */
    public function pop($queue = null)
    {
        /**
         * Not used with CI jobs
         */
//        $queue = $this->getQueue($queue);
//
//        return $this->database->transaction(function () use ($queue) {
//            if ($job = $this->getNextAvailableJob($queue)) {
//                return $this->marshalJob($queue, $job);
//            }
//        });

        return null;
    }

    /**
     * Get the next available job for the queue.
     *
     * @param  string|null  $queue
     * @return \Illuminate\Queue\Jobs\DatabaseJobRecord|null
     */
    protected function getNextAvailableJob($queue)
    {
        /**
         * Not used with CI jobs
         */
//        $job = $this->database->table($this->table)
//                    ->lock($this->getLockForPopping())
//                    ->where('queue', $this->getQueue($queue))
//                    ->where(function ($query) {
//                        $this->isAvailable($query);
//                        $this->isReservedButExpired($query);
//                    })
//                    ->orderBy('id', 'asc')
//                    ->first();
//
//        return $job ? new DatabaseJobRecord((object) $job) : null;

        return null;
    }

    /**
     * Get the lock required for popping the next job.
     *
     * @return string|bool
     */
    protected function getLockForPopping()
    {
        /**
         * Not used with CI jobs
         */
//        $databaseEngine = $this->database->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);
//        $databaseVersion = $this->database->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION);
//
//        if ($databaseEngine == 'mysql' && ! strpos($databaseVersion, 'MariaDB') && version_compare($databaseVersion, '8.0.1', '>=') ||
//            $databaseEngine == 'pgsql' && version_compare($databaseVersion, '9.5', '>=')) {
//            return 'FOR UPDATE SKIP LOCKED';
//        }
//
//        return true;

        return false;
    }

    /**
     * Modify the query to check for available jobs.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return void
     */
    protected function isAvailable($query)
    {
        /**
         * Not used with CI jobs
         */
//        $query->where(function ($query) {
//            $query->whereNull('reserved_at')
//                  ->where('available_at', '<=', $this->currentTime());
//        });
    }

    /**
     * Modify the query to check for jobs that are reserved but have expired.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return void
     */
    protected function isReservedButExpired($query)
    {
        /**
         * Not used with CI jobs
         */
//        $expiration = Carbon::now()->subSeconds($this->retryAfter)->getTimestamp();
//
//        $query->orWhere(function ($query) use ($expiration) {
//            $query->where('reserved_at', '<=', $expiration);
//        });
    }

    /**
     * Marshal the reserved job into a DatabaseJob instance.
     *
     * @param  string  $queue
     * @param  \Illuminate\Queue\Jobs\DatabaseJobRecord  $job
     * @return \Illuminate\Queue\Jobs\DatabaseJob
     */
    protected function marshalJob($queue, $job)
    {
        /**
         * Not used with CI jobs
         */
//        $job = $this->markJobAsReserved($job);
//
//        return new DatabaseJob(
//            $this->container, $this, $job, $this->connectionName, $queue
//        );
    }

    /**
     * Mark the given job ID as reserved.
     *
     * @param  \Illuminate\Queue\Jobs\DatabaseJobRecord  $job
     * @return \Illuminate\Queue\Jobs\DatabaseJobRecord
     */
    protected function markJobAsReserved($job)
    {
        /**
         * Not used with CI jobs
         */
//        $this->database->table($this->table)->where('id', $job->id)->update([
//            'reserved_at' => $job->touch(),
//            'attempts' => $job->increment(),
//        ]);
//
//        return $job;

        return null;
    }

    /**
     * Create a payload for an object-based queue handler.
     *
     * @param  object|string  $job
     * @param  string  $queue
     * @return array
     */
    protected function createObjectPayload($job, $queue, $data = null)
    {
        $payload = [
            'class_name' => 'notifications/send',
        ];

        if ($data) {
            $payload['data'] = $data;
            $payload['job'] = $job;
        } else {
            $payload['job'] = 'application\core\Queue\CallQueuedHandler@call';
            $payload['data'] = [
                'commandName' => get_class($job),
                'command' => serialize(clone $job),
            ];
        }

        return $payload;
    }

    /**
     * Create a typical, string based queue payload array.
     *
     * @param  string  $job
     * @param  string  $queue
     * @param  mixed  $data
     * @return array
     */
    protected function createStringPayload($job, $queue, $data)
    {
        return $this->createObjectPayload($job, $queue, $data);
    }

    /**
     * Delete a reserved job from the queue.
     *
     * @param  string  $queue
     * @param  string  $id
     * @return void
     *
     * @throws \Throwable
     */
    public function deleteReserved($queue, $id)
    {
        /**
         * Not used with CI jobs
         */
//        $this->database->transaction(function () use ($id) {
//            if ($this->database->table($this->table)->lockForUpdate()->find($id)) {
//                $this->database->table($this->table)->where('id', $id)->delete();
//            }
//        });
    }

    /**
     * Get the queue or return the default.
     *
     * @param  string|null  $queue
     * @return string
     */
    public function getQueue($queue)
    {
        return $queue ?: $this->default;
    }

    /**
     * Get the underlying database instance.
     *
     * @return \Illuminate\Database\Connection
     */
    public function getDatabase()
    {
        return $this->database;
    }
}
