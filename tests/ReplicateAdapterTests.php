<?php

use League\Flysystem\Config;
use League\Flysystem\Replicate\ReplicateAdapter;

class ReplicateAdapterTests extends \PHPUnit_Framework_TestCase
{
    protected $adapter;
    protected $source;
    protected $replica;

    public function setup()
    {
        $this->source = Mockery::mock('League\\Flysystem\\AdapterInterface');
        $this->replica = Mockery::mock('League\\Flysystem\\AdapterInterface');
        $this->adapter = new ReplicateAdapter($this->source, $this->replica);
    }

    public function callProvider()
    {
        return [
            'write'         => ['write', true, 3],
            'read'          => ['read', false, 1],
            'readStream'    => ['readStream', false, 1],
            'getVisibility' => ['getVisibility', false, 1],
            'setVisibility' => ['setVisibility', true, 2],
            'getSize'       => ['getSize', false, 1],
            'getMimetype'   => ['getMimetype', false, 1],
            'getMetadata'   => ['getMetadata', false, 1],
            'getTimestamp'  => ['getTimestamp', false, 1],
            'rename'        => ['rename', true, 2],
            'copy'          => ['copy', true, 2],
            'deleteDir'     => ['deleteDir', true, 1],
            'createDir'     => ['createDir', true, 2],
            'has'           => ['has', false, 1],
            'listContents'  => ['listContents', false, 2],
        ];
    }

    /**
     * @dataProvider callProvider
     */
    public function testMethodDeligation($method, $useReplica, $arguments)
    {
        $expected = 'result';
        $parameters = array_pad([], $arguments - 1, 'value');
        $parameters[] = new Config();

        $call = $this->source->shouldReceive($method)->twice();
        $call = call_user_func_array([$call, 'with'], $parameters);
        $call->andReturn(false, $expected);

        if ($useReplica === true) {
            $replicaCall = $this->replica->shouldReceive($method)->once();
            $replicaCall = call_user_func_array([$replicaCall, 'with'], $parameters);
            $replicaCall->andReturn($expected);
        }

        $this->assertFalse(call_user_func_array([$this->adapter, $method], $parameters));
        $this->assertEquals($expected, call_user_func_array([$this->adapter, $method], $parameters));
    }

    public function testGetSourceAdapter()
    {
        $this->assertEquals($this->source, $this->adapter->getSourceAdapter());
    }

    public function testGetReplicaAdapter()
    {
        $this->assertEquals($this->source, $this->adapter->getReplicaAdapter());
    }

    public function testMethodUpdateSourceWillNotUpdate()
    {
        $this->source->shouldReceive('update')->once()->andReturn(false);

        $this->assertFalse(call_user_func_array([$this->adapter, 'update'], ['value', 'value', new Config()]));
    }

    public function testMethodUpdateSourceWillUpdateAndReplicaWillUpdate()
    {
        $this->source->shouldReceive('update')->once()->andReturn(true);
        $this->replica->shouldReceive('has')->once()->andReturn(true);
        $this->replica->shouldReceive('update')->once()->andReturn(true);

        $this->assertTrue(call_user_func_array([$this->adapter, 'update'], ['value', 'value', new Config()]));
    }

    public function testMethodUpdateSourceWillUpdateAndReplicaWillWrite()
    {
        $this->source->shouldReceive('update')->once()->andReturn(true);
        $this->replica->shouldReceive('has')->once()->andReturn(false);
        $this->replica->shouldReceive('write')->once()->andReturn(true);

        $this->assertTrue(call_user_func_array([$this->adapter, 'update'], ['value', 'value', new Config()]));
    }

    public function testMethodUpdateStreamSourceWillNotUpdate()
    {
        $this->source->shouldReceive('updateStream')->once()->andReturn(false);

        $this->assertFalse(call_user_func_array([$this->adapter, 'updateStream'], ['value', 'value', new Config()]));
    }

    public function testMethodUpdateStreamSourceWillUpdateAndReplicaWillUpdate()
    {
        $this->source->shouldReceive('updateStream')->once()->andReturn(true);
        $this->replica->shouldReceive('has')->once()->andReturn(true);
        $this->replica->shouldReceive('updateStream')->once()->andReturn(true);

        $this->assertTrue($this->adapter->updateStream('value', fopen('data:text/plain,value', 'r+'), new Config()));
    }

    public function testMethodUpdateStreamSourceWillUpdateAndReplicaWillWrite()
    {
        $this->source->shouldReceive('updateStream')->once()->andReturn(true);
        $this->replica->shouldReceive('has')->once()->andReturn(false);
        $this->replica->shouldReceive('writeStream')->once()->andReturn(true);

        $this->assertTrue($this->adapter->updateStream('value', fopen('data:text/plain,value', 'r+'), new Config()));
    }

    public function testMethodUpdateStreamSourceWillWriteAndEnsureSeekableWillFail()
    {
        stream_wrapper_register('test', 'NonSeekableStream');

        $this->source->shouldReceive('updateStream')->once()->andReturn(true);
        $this->source->shouldReceive('readStream')->once()->andReturn(fopen('data:text/plain,value', 'r+'));

        $this->assertFalse($this->adapter->updateStream('value', fopen('test://value', 'r+'), new Config()));

        stream_wrapper_unregister('test');
    }

    public function testMethodWriteStreamSourceWillWriteAndReplicaWillWrite()
    {
        $this->source->shouldReceive('writeStream')->once()->andReturn(true);
        $this->replica->shouldReceive('writeStream')->once()->andReturn(true);

        $this->assertTrue($this->adapter->writeStream('value', fopen('data:text/plain,value', 'r+'), new Config()));
    }

    public function testMethodWriteStreamSourceWillNotWrite()
    {
        $this->source->shouldReceive('writeStream')->once()->andReturn(false);

        $this->assertFalse($this->adapter->writeStream('value', fopen('data:text/plain,value', 'r+'), new Config()));
    }

    public function testMethodWriteStreamSourceWillWriteAndEnsureSeekableWillFail()
    {
        stream_wrapper_register('test', 'NonSeekableStream');

        $this->source->shouldReceive('writeStream')->once()->andReturn(true);
        $this->source->shouldReceive('readStream')->once()->andReturn(fopen('data:text/plain,value', 'r+'));

        $this->assertFalse($this->adapter->writeStream('value', fopen('test://value', 'r+'), new Config()));

        stream_wrapper_unregister('test');
    }

    public function testMethodDeleteSourceWillNotDelete()
    {
        $this->source->shouldReceive('delete')->once()->andReturn(false);

        $this->assertFalse(call_user_func_array([$this->adapter, 'delete'], ['value']));
    }

    public function testMethodDeleteSourceWillDeleteAndReplicaWillDelete()
    {
        $this->source->shouldReceive('delete')->once()->andReturn(true);
        $this->replica->shouldReceive('has')->once()->andReturn(true);
        $this->replica->shouldReceive('delete')->once()->andReturn(true);

        $this->assertTrue(call_user_func_array([$this->adapter, 'delete'], ['value']));
    }

    public function testMethodDeleteSourceWillDeleteAndReplicaWillNotDelete()
    {
        $this->source->shouldReceive('delete')->once()->andReturn(true);
        $this->replica->shouldReceive('has')->once()->andReturn(false);

        $this->assertTrue(call_user_func_array([$this->adapter, 'delete'], ['value']));
    }
}

class NonSeekableStream
{
    public function stream_open($uri, $mode, $options, &$opened_path)
    {
        return true;
    }

    public function stream_seek($offset, $whence = SEEK_SET)
    {
        return false;
    }

    public function stream_eof()
    {
        return false;
    }
}
