<?php

namespace App\Http\Controllers;

use App\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;


class BlogsParserController extends Controller
{
    public $bloger = "";
    public $bloger_profile = "";
    public $bloger_id = "";
    public $bloger_check;
    public $lj_api = "";
    public $proxy = "https://awmproxy.com/freeproxy_a0d1cab10cfee52.txt";

    public function index()
    {
        for ($i = 0; $i < 5; $i++) {
            $blogers = DB::select('select * from blogers order by id desc limit 1');
            if (empty($blogers[0])) {
                (int)$blogers[0] = 0;
                $page = 1;
            } else {
                $page = (int)$blogers[0]->page + 1;
            }

            $proxy = $this->proxy_rand();

            try {
                $client = new \GuzzleHttp\Client();
                $lg_body = $client->request('GET', 'https://www.livejournal.com/ratings/users?page=' . $page . "&country=cyr", [
                    'proxy' => $proxy,
                    'http_errors' => false,
                    'connect_timeout' => 15
                ])->getBody();

            } catch (\GuzzleHttp\Exception\RequestException $e) {
                //echo $e->getMessage();
                continue;
            }

            $html = new \Htmldom($lg_body);

            foreach ($html->find('.i-ljuser-username') as $element) {
                $urls[] = $element->href;
            }

            if (count($urls) < 10) {
                die("Кончился парсинг не странице " . $page . "!");
            }

            if ($urls[0] <> "") {
                foreach ($urls as $url) {
                    DB::insert('insert into blogers (url, platform, page, `check`) values (?, ?, ?, ?)', [$url, 'lj', $page, 0]);
                }
            }

            unset($urls);
        }
    }

    public function dead_profiles()
    {

        for ($i = 0; $i < 1; $i++) {

            $client = new \GuzzleHttp\Client();
            $this->get_profile_username();
            $bloger = $this->bloger;
            $bloger_profile = $this->bloger_profile;
            $bloger_id = $this->bloger_id;
            $bloger_check = $this->bloger_check;

            $lj_access = json_decode(\App\Setting::where('setting_key', 'lj_access')->get(), true);
            $lj_access_session = $lj_access[0]['setting_value'];

            $proxy = $this->proxy_rand();

            try {

                $request = '[{"jsonrpc":"2.0","method":"profile.get_friends","params":{"user":"' . $bloger . '","get_list":"subscribersof","mode_full":true,"auth_token":"' . $lj_access_session . '"},"id":3}]';
                $request_arr = \GuzzleHttp\json_decode($request);

                $result = $client->post('https://www.livejournal.com/__api/', [
                    'json' => $request_arr,
                    'proxy' => $proxy,
                ])->getBody();

            } catch (\GuzzleHttp\Exception\RequestException $e) {
                continue;
            }

            $result_arr = json_decode($result, true);


            if (isset($result_arr[0]['error']['message'])) {
                if ($result_arr[0]['error']['message'] != '') {
                    $this->get_lj_api_access($bloger_profile);
                    die;
                }
            }

            if(isset($rusult_arr[0]['result'])){
                $this->update_check();
            }

            $dead_list = $result_arr[0]['result']['list'];
            foreach ($dead_list as $dead_profile) {
                if ($dead_profile['is_invisible'] == 1) {
                    $dead_profiles_list[] = $dead_profile['profile_url'];
                }
            }

            if(count($dead_profiles_list)>1){
                foreach ($dead_profiles_list as $profile){
                    DB::insert("insert into dead_profiles (parent,profile) values (?,?)",[$bloger_profile,$profile]);
                }
            }

            $this->update_check_final();

        }
    }

    public function get_profile_username()
    {
        $blogers = DB::select('SELECT id,url,`check` FROM blogers WHERE `check`<20 ORDER BY `check`,id ASC limit 1');
        preg_match('/https\:\/\/(.*)\./Uis', $blogers[0]->url, $bloger);
        $this->bloger = $bloger[1];
        $this->bloger_id = $blogers[0]->id;
        $this->bloger_profile = $blogers[0]->url . "/profile";
        $this->bloger_check = $blogers[0]->check;
    }

    public function get_lj_api_access($bloger_profile)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $bloger_profile)->getBody();
        preg_match('/var p \= (.*)\, i\;/', $response, $json);
        $json = str_replace('var p =', '', $json[0]);
        $json = str_replace(', i;', '', $json);
        $json_arr = \GuzzleHttp\json_decode($json, true);
        $auth_token = $json_arr['auth_token'];
        \App\Setting::where('setting_key', 'lj_access')->update(['setting_value' => $json_arr['auth_token']]);
        return $this->lj_api = $json_arr['auth_token'];
    }

    public function proxy()
    {
        $proxy_list = file($this->proxy);
        return $proxy_list;
    }

    public function proxy_rand()
    {
        $proxy_list = $this->proxy();
        $rand = rand(0, count($proxy_list) - 1);
        return $proxy_list[$rand];
    }


    public function update_check(){
        $bloger_id = $this->bloger_id;
        $bloger_check = $this->bloger_check;

        $new_check = $bloger_check + 1;
        DB::update("update blogers set `check` = :ch where id= :id",[
                'ch'=>$new_check,
                'id'=>$bloger_id
            ]
        );
    }

    public function update_check_final(){
        $bloger_id = $this->bloger_id;
        DB::update("update blogers set `check` = :ch where id= :id",[
                'ch'=>777,
                'id'=>$bloger_id
            ]
        );
    }
}
