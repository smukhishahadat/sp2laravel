<?php

namespace shurjopay\ShurjopayLaravelPackage\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use shurjopay\ShurjopayLaravelPackage\Models\Sporder;
use shurjopay\ShurjopayLaravelPackage\Models\Spsetup;

class ShurjopayController extends Controller
{
    public function index($value='')
    {
        return view('shurjopay::shurjopay');
    }
    public function send(Request $request)
    {

        //return $request->all();
        $row = Spsetup::where('id',1)->first();

        if(!empty($row) ){

            //$row::update(['id' =>1]);
            $data=array(
                'username'=>$request->username,
                'password'=>$request->password,
                'uniquekey'=>$request->uniquekey,
                'returnurl'=>$request->returnurl,
              'ipn'=>$request->ipn,
                'updated_at'=>date('Y-m-d h:i:s')
            );

            $data_username=$request->username;
            $data_password=$request->password;
            $data_uniquekey=$request->uniquekey;
            $data_returnurl=$request->returnurl;
            $data_ipn=$request->ipn;

            Spsetup::where('id', 1)->update($data);
            $this->overWriteEnvFile("MERCHANT_USERNAME", $data_username);
            $this->overWriteEnvFile("MERCHANT_PASSWORD", $data_password);
            $this->overWriteEnvFile("MERCHANT_UNIQUE_KEY", $data_uniquekey);
            $this->overWriteEnvFile("MERCHANT_RETURN_URL", $data_returnurl);
            $this->overWriteEnvFile("MERCHANT_IPN", $data_ipn);




        }
        else
        {
            $data= new Spsetup();
                 $data->username=$request->username;
                 $data->password=$request->password;
                 $data->uniquekey=$request->uniquekey;
                 $data->returnurl=$request->returnurl;
                 $data->ipn=$request->ipn;
                 $data->created_at=date('Y-m-d h:i:s');

                 $data->save();
            $data_username=$request->username;
            $data_password=$request->password;
            $data_uniquekey=$request->uniquekey;
            $data_returnurl=$request->returnurl;
            $data_ipn=$request->ipn;
            $this->overWriteEnvFile("MERCHANT_USERNAME", $data_username);
            $this->overWriteEnvFile("MERCHANT_PASSWORD", $data_password);
            $this->overWriteEnvFile("MERCHANT_UNIQUE_KEY", $data_uniquekey);
            $this->overWriteEnvFile("MERCHANT_RETURN_URL", $data_returnurl);
            $this->overWriteEnvFile("MERCHANT_IPN", $data_ipn);

/*            Spsetup::create($request->all());*/
        }
        return redirect(route('shurjopay'));
    }

    //mowmita
    //example info
/*$info = array(
'prefix' => "spay",
'currency' => "BDT",
'return_url' => "http://127.0.0.1:8000/response",
'cancel_url' => "http://127.0.0.1:8000/response",
'amount' => 10,
'order_id' => "235635464",
'discsount_amount' => 0,
'disc_percent' => 0,
'client_ip' => "http://127.0.0.1:8000",
'customer_name' => "customer_name",
'customer_phone' => "customer_phone",
'email' => "email",
'customer_address' => "customer_address",
'customer_city' => "customer_city",
'customer_state' => "customer_state",
'customer_postcode' => "customer_postcode",
'customer_country' => "customer_country",
);*/

    public function checkout($info){
        $flag=0;
        if(!isset($info['prefix']))
        {
            $flag=1;
            echo 'Please provide Prefix';
        }
        if(!isset($info['amount']))
        {
            $flag=2;
            echo 'Please provide amount';

        }
        if(!isset($info['order_id']))
        {
            $flag=3;
            echo 'Please provide order id';

        }
        if(!isset($info['customer_name']))
        {
            $flag=4;
            echo 'Please provide customer name';

        }
        if(!isset($info['customer_phone']))
        {
            $flag=5;
            echo 'Please provide customer phone';

        }
        if(!isset($info['customer_address']))
        {
            $flag=6;
            echo 'Please provide customer address';

        }
        if($flag==0)
        {
            $response = $this->getUrl($info);
            $arr = json_decode($response);
            $url = ($arr->checkout_url);
            $order_id = ($arr->sp_order_id);
            $order = new Sporder();
            $order->bank_trx_id = $order_id.rand(10000,99999) ;
            $order->order_id = $order_id ;
            $order->response = 0;

            $order->amount = $info['amount'];
            $order->inv_id = $info['order_id'];
            $order->status =0;
            $order->save();
            return redirect($url);
        }
    }
    private function getToken() {
        $user= env('MERCHANT_USERNAME');
        $pass= env('MERCHANT_PASSWORD');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://engine.shurjopayment.com/api/get_token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
    "username": "'.$user.'",
    "password": "'.$pass.'"
}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
    private function getUrl($info) {
        $response=$this->getToken();
        $arr=json_decode($response);
        $tok=($arr->token);
        $s_id=($arr->store_id);

        $info2=array(
            'token'=>$tok,
            'store_id'=>$s_id);
        $final_array=array_merge($info2, $info);
        $bodyJson=json_encode($final_array);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://engine.shurjopayment.com/api/secret-pay',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>$bodyJson,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
            exit();
        }else{
            return $response;
        }

    }
    private function verify($order_id) {
        $order_id = array(
            'order_id' => $order_id);
        $order_id=json_encode($order_id);
        $response=$this->getToken();
        $arr=json_decode($response);
        $tok=($arr->token);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://engine.shurjopayment.com/api/verification',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>$order_id
,
            CURLOPT_HTTPHEADER => array(
                'Authorization:Bearer '.$tok,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;



    }


    public function return($id=null)
    {
        //$actual_link = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        //$query_str = parse_url($actual_link, PHP_URL_QUERY);
       // parse_str($query_str, $query_params);
        $orderID =$id; //$query_params['order_id'];
        $response=$this->verify($orderID);
        $object=Sporder::find(1);
        $object = Sporder::where('order_id',$orderID)->first();
        $object->response=$response;
        $arr=json_decode($response);

        if(!empty($arr[0]->bank_trx_id))
        {
            $bank=($arr[0]->bank_trx_id);
            $object->bank_trx_id=$bank;
            $object->status =$arr[0]->sp_code;

        }

        $object->save();
        $inv_no=$arr[0]->customer_order_id;
   /*     print_r($object);
        exit()*/;
        $actual_link = env('MERCHANT_RETURN_URL')."/".$inv_no;
        return $response;
       // return redirect($actual_link);

    }
    public function overWriteEnvFile($type, $val)
    {
        if(env('DEMO_MODE') != 'On'){
            $path = base_path('.env');
            if (file_exists($path)) {
                $val = '"'.trim($val).'"';
                if(is_numeric(strpos(file_get_contents($path), $type)) && strpos(file_get_contents($path), $type) >= 0){
                    file_put_contents($path, str_replace(
                        $type.'="'.env($type).'"', $type.'='.$val, file_get_contents($path)
                    ));
                }
                else{
                    file_put_contents($path, file_get_contents($path)."\r\n".$type.'='.$val);
                }
            }
        }
    }
}
