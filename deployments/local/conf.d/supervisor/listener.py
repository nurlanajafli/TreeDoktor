import sys
import subprocess

def write_stdout(s):
    # only eventlistener protocol messages may be sent to stdout
    sys.stdout.write(s)
    sys.stdout.flush()

def write_stderr(s):
    sys.stderr.write(s)
    sys.stderr.flush()

def main():
    while 1:
        # transition from ACKNOWLEDGED to READY
        write_stdout('READY\n')

        # read header line and print it to stderr
        line = sys.stdin.readline()
        write_stderr(line)

        # read event payload and print it to stderr
        headers = dict([ x.split(':') for x in line.split() ])
        data = sys.stdin.read(int(headers['len']))
        write_stderr(data)

        # transition from READY to ACKNOWLEDGED
        write_stdout('RESULT 2\nOK')

        if headers["eventname"] == "PROCESS_STATE_STOPPING" or headers["eventname"] == "PROCESS_STATE_EXITED" or headers["eventname"] == "PROCESS_STATE_FATAL":
            payload = dict([ x.split(':') for x in data.split() ])
            subprocess.run(["/opt/arbostar/panic.sh", headers["eventname"], payload["processname"]], cwd="/tmp")

if __name__ == '__main__':
    main()
