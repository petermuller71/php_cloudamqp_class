<?php
#############################################################################################################
# CloudMQ / RabbitMQ
# simple static class to create queues on CloudMQ, sent data to the queue en retrieve data
#############################################################################################################

$res = CloudMQ::create_queue('q1', 0); // Create queue with name 'q1' with expiration time in seconds (0 is: dont expire)

$res = CloudMQ::publish('q1', 'test1'); // publish to the queue
$res = CloudMQ::publish('q1', 'test2');

$res = CloudMQ::get('q1', 50000); // get 50k messages from queue with name 'q1'
print "<br /><br />res $res";




class CloudMQ {

    #https://cdn.rawgit.com/rabbitmq/rabbitmq-management/v3.8.9/priv/www/api/index.html

    static private $username = 'xxx';
    static private $password = 'xxx';


    public static function get($qname, $count) {

        $data   = '{"count":'.$count.',"ackmode":"ack_requeue_false","encoding":"auto","truncate":50000}';
        $url    = 'https://sparrow.rmq.cloudamqp.com/api/queues/'.self::$username.'/'.$qname.'/get';
        $method = "POST";

        $res = self::curl($url, $method, $data);
        return $res;
    }


    public static function publish($qname, $data) {


        $data   = '{"properties":{},"routing_key":"'.$qname.'","payload":"'.$data.'","payload_encoding":"string"}';
        $url    = 'https://sparrow.rmq.cloudamqp.com/api/exchanges/'.self::$username.'/amq.default/publish';
        $method = "POST";

        $res = self::curl($url, $method, $data);
        return $res;
    }


    public static function create_queue($qname, $expire) {

        if ($expire == 0) {
            $data = '{"durable":true,"arguments":{} }';
        }
        else {
            $time = $expire * 1000;
            $data = '{"durable":true,"arguments":{"x-expires": '.$time.'}}';
        }

        $url = 'https://sparrow.rmq.cloudamqp.com/api/queues/'.self::$username.'/'.$qname;
        $method = "PUT";

        $res = self::curl($url, $method, $data);

        return $res;
    }


    private static function curl($url, $method, $data) {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_USERPWD, self::$username . ":" . self::$password);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data))
            );
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}
?>
