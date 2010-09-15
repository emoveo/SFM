<?php
class SFM_XML extends DomDocument implements SFM_Interface_Singleton
{
    /**
     * Singleton
     *
     * @var SFM_XML
     */
    private static $instance;
    
    private $root;
    
    private $images;
    
    private $content;
    
    private $menu;
    
    public function __construct()
    {
        parent::__construct();
        $this->root = 'data';
        $this->encoding = 'utf-8';
        $this->root = $this->appendChild($this->createElement('data'));
        $this->content = $this->root->appendChild($this->createElement('content'));
        $this->menu = $this->root->appendChild($this->createElement('menu'));
        $this->images = $this->root->appendChild($this->createElement('images'));
        echo $this->saveXML();
    }
    
    public function createNode(array $arr, $node=null)
    {
        $node = ($node === null) ? $this->root : $node;
        
        foreach ($arr as $element => $value) {
            
        }
    }
    
    public static function getInstance()
    {
        return (self::$instance === null) ? (self::$instance = new self()) : self::$instance;
    }
    
}
?>