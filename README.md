# wsm

Whitespace Assembly Language

## Usage

You can use wsm to compile a wsm file into ws:

    $ php bin/wsm examples/hworld.wsm > hworld.ws

You can then run that ws file with any whitespace interpreter, such as [whitespace-php](https://github.com/igorw/whitespace-php):

    $ php /path/to/interpreter hworld.ws
    Hello, world of spaces!
