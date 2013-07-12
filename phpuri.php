<?php
/**
* @package phpUri
*/
class phpUri{
  var $scheme, $authority, $path, $query, $fragment;

  function __construct($string){
    preg_match_all('/^(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?$/', $string ,$m);
    $this->scheme = $m[2][0];
    $this->authority = $m[4][0];
    $this->path = $m[5][0];
    $this->query = $m[7][0];
    $this->fragment = $m[9][0];
  }

  public static function parse($string){
    $uri = new phpUri($string);
    return $uri;
  }

  function join($string){
    $uri = new phpUri($string);
    switch(true){
      case !empty($uri->scheme): break;
      case !empty($uri->authority): break;
      case empty($uri->path):
        $uri->path = $this->path;
        if(empty($uri->query)) $uri->query = $this->query;
      case strpos($uri->path, '/') === 0: break;
      default:
        $base_path = $this->path;
        if(strpos($base_path, '/') === false){
          $base_path = '';
        } else {
          $base_path = preg_replace ('/\/[^\/]+$/' ,'/' , $base_path);
        }
        if(empty($base_path) && empty($this->authority)) $base_path = '/';
        $uri->path = $base_path . $uri->path; 
    }
    if(empty($uri->scheme)){
      $uri->scheme = $this->scheme;
      if(empty($uri->authority)) $uri->authority = $this->authority;
    }
    return $uri->to_str();
  }

  function normalize_path($path){
    if(empty($path)) return '';
    $normalized_path = $path;
    $normalized_path = preg_replace('`//+`', '/' , $normalized_path, -1, $c0);
    $normalized_path = preg_replace('`^/\\.\\.?/`', '/' , $normalized_path, -1, $c1);
    $normalized_path = preg_replace('`/\\.(/|$)`', '/' , $normalized_path, -1, $c2);
    $normalized_path = preg_replace('`/[^/]*?/\\.\\.(/|$)`', '/' , $normalized_path, -1, $c3);
    $num_matches = $c0 + $c1 + $c2 + $c3;
    return ($num_matches > 0) ? $this->normalize_path($normalized_path) : $normalized_path;
  }

  function to_str(){
    $ret = "";
    if(!empty($this->scheme)) $ret .= "$this->scheme:";
    if(!empty($this->authority)) $ret .= "//$this->authority";
    $ret .= $this->normalize_path($this->path);
    if(!empty($this->query)) $ret .= "?$this->query";
    if(!empty($this->fragment)) $ret .= "#$this->fragment";
    return $ret;
  }
}
?>