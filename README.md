# Server Status

| `master` | `develop` |
|----------|-----------|
| [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/ServerStatus/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Innmind/ServerStatus/?branch=master) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/ServerStatus/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/ServerStatus/?branch=develop) |
| [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/ServerStatus/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Innmind/ServerStatus/?branch=master) | [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/ServerStatus/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/ServerStatus/?branch=develop) |
| [![Build Status](https://scrutinizer-ci.com/g/Innmind/ServerStatus/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Innmind/ServerStatus/build-status/master) | [![Build Status](https://scrutinizer-ci.com/g/Innmind/ServerStatus/badges/build.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/ServerStatus/build-status/develop) |

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
    Server\Process\Pid
};
use Innmind\TimeContinuum\TimeContinuum\Earth;

$server = (new ServerFactory(new Earth))->make();

$server->cpu()->user(); //percentage of the cpu used by the user
$server->cpu()->system(); //percentage of the cpu used by the system
$server->cpu()->idle(); //percentage of the cpu not used
$server->cput()->cores(); //number of cores available

$server->memory()->total(); //total memory of the server
$server->memory()->wired(); //memory that cannot be taken out of ram
$server->memory()->active(); //memory that is used by processes
$server->memory()->free(); //memory that is not used
$server->memory()->swap(); //memory that is used and located on disk
$server->memory()->used(); //total - free

$server->loadAverage()->lastMinute();
$server->loadAverage()->lastFiveMinutes();
$server->loadAverage()->lastFifteenMinutes();

$server->disk()->get(new MountPoint('/'))->size(); //total size of the volume
$server->disk()->get(new MountPoint('/'))->available();
$server->disk()->get(new MountPoint('/'))->used();
$server->disk()->get(new MountPoint('/'))->usage(); //percentage of space being used

$server->processes()->get(new Pid(1))->user(); //root in this case
$server->processes()->get(new Pid(1))->cpu(); //percentage
$server->processes()->get(new Pid(1))->memory(); //percentage
$server->processes()->get(new Pid(1))->start(); //point in time at which the process started
$server->processes()->get(new Pid(1))->command();

$server->tmp(); //path to temp directory
```
