<?php
/**
 * 通用分页类
 * @copyright rareMVC
 * @author duwei
 *
 */
class rPager{
    protected  $total;      //总数
    protected $size;       //每页显示数量
    protected $linkNum=10;  //链接数量
    protected $page;       //当前页
    
    protected $totalPage; //总页数
    
    protected $label="p"; //页面 变量 $_GET 参数 
    protected $uri;
    protected $startPage; //开始页面
    protected $endPage;   //结束页面
    protected $startNum;
    protected $endNum;  
    
    protected  $_curLineNum=0;//当前的行号

    /**
     * $pageInfo=array('total'=>1000,'size'=>10,"p"=>1);
     * @param array $pageInfo
     */
   public function __construct($pageInfo){
       $this->total=$pageInfo['total'];
       isset($pageInfo['label']) && $this->label=$pageInfo['label'];
       $this->page=(int)(isset($pageInfo['page'])?$pageInfo['page']:(isset($_GET[$this->label])?$_GET[$this->label]:1));
       if($this->page<=0) $this->page=1;
       $this->size=$pageInfo['size'];
       $this->_count();
   }
   
   public function getStartNum(){
     return $this->startNum;
   }
   
   public function getTotalPage(){
     return $this->totalPage;
   }
   
   /**
    * 获取当前行号的编号
    * @param boolean $rev 是否倒计数
    */
   public function getLineNum($rev=false){
      $this->_curLineNum++;
      return $rev?($this->getTotalNum()-$this->getStartNum()-$this->_curLineNum+2):$this->getStartNum()+$this->_curLineNum-1;
   }
   
   public function getTotalNum(){
     return $this->total;
   }
   
   public function __set($key,$value){
     $this->$key=$value;
     $this->_count();
     return $this;
   }
   
   protected  function _count(){
      $this->totalPage=ceil($this->total/$this->size);
      if($this->page>$this->totalPage)$this->page=$this->totalPage;
      $this->startNum=($this->page-1)*$this->size+1;
      $subLinkNum=intval($this->linkNum/2);
      $this->startPage=max(min($this->page-$subLinkNum,$this->totalPage-$this->linkNum),1);
      $this->endPage=min(max($this->linkNum+1,$this->page+$subLinkNum),$this->totalPage);
      $this->endNum=min($this->startNum+$this->size,$this->total);
   }
   
   public function __toString(){
      $this->_count();
      if($this->total<1)return "";
      if($this->totalPage<2)return "";
      $html="<div class='rPager'><ul>";
      $html.="<li class='info'>({$this->startNum}-{$this->endNum}|{$this->total})</li>";
      $html.="<li class='first'>".$this->_page_link($this->page>1, 1, "|&lt;&lt;")."</li>";
      $html.="<li class='prev'>".$this->_page_link($this->page>1, $this->page-1, "&lt;&lt;")."</li>";
      
      for($i=$this->startPage;$i<=$this->endPage;$i++){
          if($i==$this->page){
             $html .="<li class='current' rel='{$this->makeUrl($this->page)}'>{$this->page}</li>";
          }else{
             $html .="<li><a href='".$this->makeUrl($i)."'>{$i}</a></li>";
          }
       }
       
      $html .= "<li class='next'>".$this->_page_link($this->page<$this->totalPage, $this->page+1, "&gt;&gt;")."</li>";
      $html .= "<li class='last'>".$this->_page_link($this->page<$this->totalPage, $this->totalPage, "&gt;&gt;|")."</li>";
       
      $html.="</ul></div><div style='clear:both'></div>\n";
      return $html;
   }
   
   private function _page_link($isLink,$page,$txt){
     if($isLink){
        return "<a href='".$this->makeUrl($page)."'>{$txt}</a>";
     }else{
       return $txt;
     }
   }
   
    
   protected function makeUrl($p){
        $uri = empty($this->uri) ?$_SERVER['REQUEST_URI'] :$this->uri;
        $tmp=parse_url($uri);
        $query=array();
        if(isset($tmp['query'])){
            parse_str($tmp['query'],$query);
         }
        $query[$this->label]=max($p,1);
        $prep=(empty($tmp['path'])?'':$tmp['path'])."?".http_build_query($query);
       return $prep;
   }
   
}