<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use mikehaertl\shellcommand\Command;

class CommandTest extends TestCase
{
    // Create command from command string
    public function testCanPassCommandStringToConstructor()
    {
        $command = new Command('/bin/ls -l');

        $this->assertEquals('/bin/ls -l', $command->getExecCommand());
    }
    public function testCanPassCommandOptionToConstructor()
    {
        $command = new Command(array(
            'command' => '/bin/ls -l',
        ));

        $this->assertEquals('/bin/ls -l', $command->getExecCommand());
    }
    public function testCanSetCommand()
    {
        $command = new Command();
        $command->setCommand('/bin/ls -l');

        $this->assertEquals('/bin/ls -l', $command->getExecCommand());
    }

    // Options
    public function testCanSetOptions()
    {
        $command = new Command;
        $command->setOptions(array(
            'command' => 'echo',
            'escapeArgs' => false,
            'procEnv' => array('TESTVAR' => 'test'),
            'args' => '-n $TESTVAR',
        ));
        $this->assertEquals('echo -n $TESTVAR', $command->getExecCommand());
        $this->assertFalse($command->escapeArgs);
        $this->assertFalse($command->getExecuted());
        $this->assertTrue($command->execute());
        $this->assertTrue($command->getExecuted());
        $this->assertEquals('test', $command->getOutput());
    }
    public function testCanPassOptionsToConstructor()
    {
        $command = new Command(array(
            'command' => 'echo',
            'args' => '-n $TESTVAR',
            'escapeArgs' => false,
            'procEnv' => array('TESTVAR' => 'test'),
        ));
        $this->assertEquals('echo -n $TESTVAR', $command->getExecCommand());
        $this->assertFalse($command->escapeArgs);
        $this->assertFalse($command->getExecuted());
        $this->assertTrue($command->execute());
        $this->assertTrue($command->getExecuted());
        $this->assertEquals('test', $command->getOutput());
    }


    // Arguments
    public function testCanAddArguments()
    {
        $command = new Command(array(
            'locale' => 'en_US.UTF-8',
        ));
        $command->setCommand('test');
        $command->setArgs('--arg1=x');
        $command->addArg('--a');
        $command->addArg('--a', '中文字äüp');
        $command->addArg('--a', array("v'1",'v2','v3'));
        $command->addArg('-b=','v', false);
        $command->addArg('-b=', array('v4','v5','v6'));
        $command->addArg('-c', '');
        $command->addArg('some name', null, true);
        $this->assertEquals("--arg1=x '--a' '--a' '中文字äüp' '--a' 'v'\''1' 'v2' 'v3' -b=v '-b'='v4' 'v5' 'v6' '-c' '' 'some name'", $command->getArgs());
        $this->assertEquals("test --arg1=x '--a' '--a' '中文字äüp' '--a' 'v'\''1' 'v2' 'v3' -b=v '-b'='v4' 'v5' 'v6' '-c' '' 'some name'", $command->getExecCommand());
    }
    public function testCanResetArguments()
    {
        $command = new Command();
        $command->addArg('--demo');
        $command->addArg('-name=test');
        $command->setArgs('--arg1=x');
        $this->assertEquals("--arg1=x", $command->getArgs());
    }
    public function testCanDisableEscaping()
    {
        $command = new Command();
        $command->escapeArgs = false;
        $command->addArg('--a');
        $command->addArg('--a', 'v');
        $command->addArg('--a', array("v1",'v2','v3'));
        $command->addArg('-b=','v', true);
        $command->addArg('-b=', array('v4','v5','v6'));
        $command->addArg('some name', null, true);
        $this->assertEquals("--a --a v --a v1 v2 v3 '-b'='v' -b=v4 v5 v6 'some name'", $command->getArgs());
    }
    public function testCanPreventCommandInjection()
    {
        $command = new Command(array(
            'command' => 'curl',
        ));
        $command->addArg('http://example.com --wrong-argument || echo "RCE 1"');
        $this->assertEquals("'http://example.com --wrong-argument || echo \"RCE 1\"'", $command->getArgs());

        $command = new Command(array(
            'command' => 'curl',
        ));
        $command->addArg('http://example.com');
        $command->addArg('--header foo --wrong-argument || echo "RCE 2" ||', 'bar');
        $this->assertEquals("'http://example.com' '--header foo --wrong-argument || echo \"RCE 2\" ||' 'bar'", $command->getArgs());
    }
    public function testCanRunCommandWithArguments()
    {
        $command = new Command('ls');
        $command->addArg('-l');
        $command->addArg('-n');
        $this->assertEquals("ls '-l' '-n'", $command->getExecCommand());
        $this->assertFalse($command->getExecuted());
        $this->assertTrue($command->execute());
        $this->assertTrue($command->getExecuted());
    }

    // Output / error / exit code
    public function testCanRunValidCommand()
    {
        $dir = __DIR__;
        $command = new Command("/bin/ls $dir/Command*");

        $this->assertFalse($command->getExecuted());
        $this->assertTrue($command->execute());
        $this->assertTrue($command->getExecuted());
        $this->assertEquals("$dir/CommandTest.php", $command->getOutput());
        $this->assertEquals("$dir/CommandTest.php\n", $command->getOutput(false));
        $this->assertEmpty($command->getError());
        $this->assertEmpty($command->getStdErr());
        $this->assertEquals(0, $command->getExitCode());
    }
    public function testCanNotRunEmptyCommand()
    {
        $command = new Command('');
        $this->assertFalse($command->execute());
        $this->assertEquals('Could not locate any executable command', $command->getError());
    }
    public function testCanNotRunNotExistantCommand()
    {
        $command = new Command('/does/not/exist');
        $this->assertFalse($command->getExecuted());
        $this->assertFalse($command->execute());
        $this->assertFalse($command->getExecuted());
        $this->assertNotEmpty($command->getError());
        $this->assertNotEmpty($command->getStdErr());
        $this->assertEmpty($command->getOutput());
        $this->assertEquals(127, $command->getExitCode());
    }
    public function testCanNotRunInvalidCommand()
    {
        $command = new Command('ls --this-does-not-exist');
        $this->assertFalse($command->getExecuted());
        $this->assertFalse($command->execute());
        $this->assertFalse($command->getExecuted());
        $this->assertNotEmpty($command->getError());
        $this->assertNotEmpty($command->getStdErr());
        $this->assertEmpty($command->getOutput());
        $this->assertEquals(2, $command->getExitCode());
    }
    public function testCanCastToString()
    {
        $command = new Command('ls');
        $command->addArg('-l');
        $command->addArg('-n');
        $this->assertEquals("ls '-l' '-n'", (string)$command);
    }

    // Exec
    public function testCanRunValidCommandWithExec()
    {
        $dir = __DIR__;
        $command = new Command("/bin/ls $dir/Command*");
        $command->useExec = true;

        $this->assertFalse($command->getExecuted());
        $this->assertTrue($command->execute());
        $this->assertTrue($command->getExecuted());
        $this->assertEquals("$dir/CommandTest.php", $command->getOutput());
        $this->assertEmpty($command->getError());
        $this->assertEmpty($command->getStdErr());
        $this->assertEquals(0, $command->getExitCode());
    }
    public function testCanNotRunNotExistantCommandWithExec()
    {
        $command = new Command('/does/not/exist');
        $command->useExec = true;
        $this->assertFalse($command->getExecuted());
        $this->assertFalse($command->execute());
        $this->assertFalse($command->getExecuted());
        $this->assertNotEmpty($command->getError());
        $this->assertNotEmpty($command->getStdErr());
        $this->assertNotEmpty($command->getOutput());
        $this->assertEquals(127, $command->getExitCode());
    }
    public function testCanNotRunInvalidCommandWithExec()
    {
        $command = new Command('ls --this-does-not-exist');
        $command->useExec = true;
        $this->assertFalse($command->getExecuted());
        $this->assertFalse($command->execute());
        $this->assertFalse($command->getExecuted());
        $this->assertNotEmpty($command->getError());
        $this->assertNotEmpty($command->getStdErr());
        $this->assertNotEmpty($command->getOutput());
        $this->assertEquals(2, $command->getExitCode());
    }

    // Proc
    public function testCanProvideProcEnvVars()
    {
        $command = new Command('echo $TESTVAR');
        $command->procEnv = array('TESTVAR' => 'testvalue');
        $this->assertTrue($command->execute());
        $this->assertEquals("testvalue", $command->getOutput());
    }
    public function testCanProvideProcDir()
    {
        $tmpDir = sys_get_temp_dir();
        $command = new Command('pwd');
        $command->procCwd = $tmpDir;
        $this->assertFalse($command->getExecuted());
        $this->assertTrue($command->execute());
        $this->assertTrue($command->getExecuted());
        $this->assertEquals($tmpDir, $command->getOutput());
    }
    public function testCanRunCommandWithStandardInput()
    {
        $command = new Command('/bin/cat');
        $command->addArg('-T');
        $command->setStdIn("\t");
        $this->assertTrue($command->execute());
        $this->assertTrue($command->getExecuted());
        $this->assertEquals("^I", $command->getOutput());
    }
    public function testCanRunCommandWithStandardInputStream()
    {
        $string = str_repeat('01234567890abcdef', 16 * 1024); // 16 * 16 * 1024 = 256KB
        $tmpfile = tmpfile();
        fwrite($tmpfile, $string);
        fseek($tmpfile, 0);
        $command = new Command('/bin/cat');
        $command->setStdIn($tmpfile);
        $this->assertTrue($command->execute());
        $this->assertTrue($command->getExecuted());
        $this->assertEquals(strlen($string), strlen($command->getOutput()));
        fclose($tmpfile);
    }

    public function testCanRunCommandWithBigInputAndOutput()
    {
        $string = str_repeat('01234567890abcdef', 16 * 1024); // 16 * 16 * 1024 = 256KB
        $command = new Command('/bin/cat');
        $command->setStdIn($string);
        $this->assertTrue($command->execute());
        $this->assertTrue($command->getExecuted());
        $this->assertEquals(strlen($string), strlen($command->getOutput()));
    }
    public function testCanRunLongRunningCommandWithBigInputAndOutput()
    {
        $string = str_repeat('01234567890abcdef', 16 * 1024); // 16 * 16 * 1024 = 256KB
        $command = new Command('/bin/cat; echo "start" ; sleep 2 ; echo "done"');
        $command->setStdIn($string);
        $this->assertTrue($command->execute());
        $this->assertTrue($command->getExecuted());
        $expected = $string . "start\ndone";
        $this->assertEquals(strlen($expected), strlen($command->getOutput()));
    }
    public function testCanRunLongRunningCommandWithStandardInputStream()
    {
        $string = str_repeat('01234567890abcdef', 16 * 1024); // 16 * 16 * 1024 = 256KB
        $tmpfile = tmpfile();
        fwrite($tmpfile, $string);
        fseek($tmpfile, 0);
        $command = new Command('/bin/cat; echo "start" ; sleep 2 ; echo "done"');
        $command->setStdIn($tmpfile);
        $this->assertTrue($command->execute());
        $this->assertTrue($command->getExecuted());
        $expected = $string . "start\ndone";
        $this->assertEquals(strlen($expected), strlen($command->getOutput()));
        fclose($tmpfile);
    }

    public function testCanTerminateLongRunningCommandWithTimeout()
    {
        $command = new Command('sleep 5');
        $command->timeout = 2;
        $startTime = time();
        $this->assertFalse($command->execute());
        $stopTime = time();
        $this->assertFalse($command->getExecuted());
        $this->assertNotEquals(0, $command->getExitCode());
        $this->assertStringStartsWith('Command terminated by signal', $command->getError());
        $this->assertStringStartsWith('Command terminated by signal', $command->getStdErr());
        $this->assertEmpty($command->getOutput());
        $this->assertEquals(2, $stopTime - $startTime);
    }
}
