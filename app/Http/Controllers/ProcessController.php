<?php
namespace App\Http\Controllers;
use Google\Cloud\Core\ServiceBuilder;

class ProcessController extends Controller{   

    function getInstaData($tag = 'nodejs') {
        $insta_source = file_get_contents('https://www.instagram.com/explore/tags/'.$tag.'/?__a=1&client_id=1964002447263353|B2ZteK6prxuBo-adVnsiviQeOPo'); 
        $insta_array = json_decode($insta_source, true);
        return $insta_array;
    }

    public function query(){
        $tag = "nodejs";
        $results_array = $this->getInstaData($tag);
        $limit = 20;
        $allData = [];
        
        for ($i=$limit; $i >= 0; $i--) {
          if(array_key_exists($i,$results_array["graphql"]["hashtag"]["edge_hashtag_to_media"]["edges"])){
            $latest_array = $results_array["graphql"]["hashtag"]["edge_hashtag_to_media"]["edges"][$i]["node"];
            $caption = $latest_array['edge_media_to_caption']['edges'][0]["node"]["text"];
            $captionSetiment = $this->sentiment($caption);
              $Posts = [
                "postData" =>[
                    "image"=>$latest_array['display_url'],
                    "thumbnail"=>$latest_array['thumbnail_src'],
                    "instagram_id"=>$latest_array['id'],
                    "caption"=> $caption
                ],
                "captionSentiments" => $captionSetiment
            ]; 
      
              $allData[] = $Posts;
          }
        }

        return $allData;
    }

    public function sentiment($text) { 
        $sentimentData = [];

        if(empty($text)){
            $sentimentData["Score"] = "0";
            $sentimentData["Magnitude"] = "0";
        } else{
            //change motionwares-93ccc3feab13.json to your JSON credentials file downloaded after setting up a service account key 
            //change social-connect-183309 to your project ID found in your google console
            $cloud = new ServiceBuilder(['keyFilePath' => base_path('motionwares-93ccc3feab13.json'),'projectId' => 'social-connect-183309']);
            $language = $cloud->language();
            $annotation = $language->analyzeSentiment($text);        
            $sentiment = $annotation->sentiment();

            $sentimentData["Score"] = $sentiment['score'];
            $sentimentData["Magnitude"] = $sentiment['magnitude'];
        }

        return $sentimentData;
    }

}
