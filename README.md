# Server Status

[![Build Status](https://github.com/innmind/serverstatus/workflows/CI/badge.svg?branch=master)](https://github.com/innmind/serverstatus/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/innmind/serverstatus/branch/develop/graph/badge.svg)](https://codecov.io/gh/innmind/serverstatus)
[![Type Coverage](https://shepherd.dev/github/innmind/serverstatus/coverage.svg)](https://shepherd.dev/github/innmind/serverstatus)

Give an easy access to the cpu, memory, disk usages and the list of processes running on the machine.

**Note**: only works for Mac OSX and Linux for now.

## Installation

```sh
composer require innmind/server-status
```

## Usage

```php
use Innmind\Server\Status\{
    ServerFactory,
    Server\Disk\Volume\MountPoint,
    Server\Process\Pid,
};
use Innmind\TimeContinuum\Earth\Clock;

$server = ServerFactory::build(new Clock);

$server->cpu()->match(
    function($cpu) {
        $cpu->user(); // percentage of the cpu used by the user
        $cpu->system(); // percentage of the cpu used by the system
        $cpu->idle(); // percentage of the cpu not used
        $cpu->cores(); // number of cores available
    },
    fn() => null, // unable to retrieve the cpu information
);

$server->memory()->match(
    function($memory) {
        $memory->total(); // total memory of the server
        $memory->active(); // memory that is used by processes
        $memory->free(); // memory that is not used
        $memory->swap(); // memory that is used and located on disk
        $memory->used(); // total - free
    },
    fn() => null, // unable to retrieve the memory information
);

$server->loadAverage()->lastMinute();
$server->loadAverage()->lastFiveMinutes();
$server->loadAverage()->lastFifteenMinutes();

$server->disk()->get(new MountPoint('/'))->match(
    function($disk) {
        $disk->size(); // total size of the volume
        $disk->available();
        $disk->used();
        $disk->usage(); // percentage of space being used
    },
    fn() => null, // the mount point doesn't exist
);

$server->processes()->get(new Pid(1))->match(
    function($process) {
        $process->user(); // root in this case
        $process->cpu(); // percentage
        $process->memory(); // percentage
        $process->start(); // point in time at which the process started
        $process->command();
    },
    fn() => null, // the process doesn't exist
);

$server->tmp(); // path to temp directory
```

You can easily log all the informations gathered via a simple decorator:

```php
use Innmind\Server\Status\Server\Logger;
use Psr\Log\LoggerInterface;

$server = new Logger($server, /** instance of LoggerInterface */);
```
