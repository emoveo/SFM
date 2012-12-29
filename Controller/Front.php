<?php
/**
 * The alone FrontController
 *
 * @author Alex Litvinenko
 * @package Generic
 */
class SFM_Controller_Front
{
    /**
     * Controller to process
     *
     * @var SFM_Controller
     */
    private $controller = null;
    
    static public function control()
    {
        try {
            $DB = SFM_DB::getInstance();
            $Router = SFM_Controller_Router::getInstance();
            $Router->parseCurrentUrl();
            $View = SFM_Template::getInstance();
            if ($Router->getLayout() !== null) {
                $View->setLayout($Router->getLayout());
            }
            
            foreach ($Router->getTemplates() as $name => $filename) {
                $View->setTpl($filename, $name);
            }
            
            list($controllerName, $methodName) = $Router->getScript();
            
            $controller = new $controllerName($Router->getParams());
            //$DB->beginTransaction();
            $controller->$methodName();
            //$DB->commit();
            switch ($View->getContentType()) {
                case SFM_Config::CONTENT_TYPE_HTML:
                    header('Content-type: text/html;charset=utf-8');
                    $View->display();
                    break;
                case SFM_Config::CONTENT_TYPE_XML:
                    header('Content-type: text/xml;charset=utf-8');
                    $View->display();
                    break;
                case SFM_Config::CONTENT_TYPE_NONE:
                    header('Content-type: text/html;charset=utf-8');
                    echo '';
                    break;
                case SFM_Config::CONTENT_TYPE_JSON:
                    header('Content-type: application/json;charset=utf-8');
                    $View->display();
                    break;
                default:
                    break;
            }
            

        } catch (SFM_Exception_PageNotFound $e) {
//          header("HTTP/1.0 404 Not Found");
            echo $e->getMessage();
        } catch (SFM_Exception_Back $e) {
            session_write_close();
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } catch (SFM_Exception_Redirect $e) {
            $url = $e->getMessage();
            
            //echo "\nRedirect to <a href=\"{$url}\">{$url}</a>";
            //die;
            //is recommended if you want to be sure the session is updated before proceeding to the redirection.     
            session_write_close();
            header("Location: $url");

        } catch (SFM_Exception_DB  $e) {
            //$DB->rollBack();
            echo 'Exception!!!<pre>' . $e->getMessage()."<br>";
            echo $e->getFile() . " on " . $e->getLine()."<br>";
            echo $e->getTraceAsString();
            
        } catch (Exception $e) {
            echo 'Exception!!!<pre>' . $e->getMessage()."<br>";
            echo $e->getFile() . " on " . $e->getLine()."<br>";
            echo $e->getTraceAsString();
        }
    }
}