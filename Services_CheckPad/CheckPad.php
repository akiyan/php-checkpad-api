<?php
require_once 'PEAR.php';
require_once 'HTTP/Client.php';

/**
 * Services_CheckPad
 *
 * @category  Services
 * @package   Services_CheckPad
 * @author    Akiyan
 * @copyright 2006 Akiyan
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: 0.1.0
 */
class Services_CheckPad
{
    /**
     * To store HTTP_Client object.
     *
     * @see Services_CheckPad
     */
    var $http;

    /**
     * checkpad domain
     */
    var $checkpad_domain = 'www.checkpad.jp';

    /**
     * constructer
     *
     * create HTTP_Request object
     */
    function Services_CheckPad()
    {
        $this->http = new HTTP_Client();
        $this->http->setDefaultHeader(
          array(
            'User-Agent' => 'Services_CheckPad 0.1.0',
          )
        );
        
        $this->login_url               = 'http://' . $this->checkpad_domain . '/';
        $this->listoftodo_url          = 'http://' . $this->checkpad_domain . '/';
        $this->addlistoftodo_url       = 'http://' . $this->checkpad_domain . '/?mode=pjt&act=add';
        $this->editlistoftodotitle_url = 'http://' . $this->checkpad_domain . '/?mode=pjt&act=edit&id=%d';
        $this->editlistoftodomemo_url  = 'http://' . $this->checkpad_domain . '/?mode=pjt&act=memo_edit&id=%d';
        $this->dellistoftodo_url       = 'http://' . $this->checkpad_domain . '/?mode=pjt&act=del&id=%d';
        $this->todo_url                = 'http://' . $this->checkpad_domain . '/?mode=pjt&act=detail&id=%d';
        $this->addtodo_url             = 'http://' . $this->checkpad_domain . '/index.php';
        $this->edittodo_url            = 'http://' . $this->checkpad_domain . '/index.php';
        $this->finishtodo_url          = 'http://' . $this->checkpad_domain . '/index.php';
        $this->unfinishtodo_url        = 'http://' . $this->checkpad_domain . '/index.php';
        $this->delnotyettodo_url       = 'http://' . $this->checkpad_domain . '/index.php';
        $this->deldonetodo_url         = 'http://' . $this->checkpad_domain . '/index.php';
    }
    
    /**
     * To login
     *
     * @param   string  $user email
     * @param   string  $pass password
     * @return  boolean
     */
    function login($email, $pass)
    {
        $param = array(
          'login_email' => $email,
          'login_pwd'   => $pass,
          'mode'        => 'sys',
          'act'         => 'login'
        );
        $res = $this->http->post($this->login_url, $param);
        if (PEAR::isError($res)) {
            return $res;
        }
        if (PEAR::isError($response = $this->http->currentResponse())) {
            return $response;
        }
        return (boolean)preg_match('/ログアウト/is', mb_convert_encoding($response['body'], 'utf-8', 'euc-jp'));
    }

    
    /**
     * get list of todo
     *
     * @param   void
     * @return  array list of todo
     *                array(
     *                  array(
     *                    'title'  => string  title
     *                    'url'    => string  detail url
     *                    'id'     => int     detail id
     *                    'left'   => int     left
     *                    'shared' => boolean shared list flag
     *                  ),
     *                  ...
     *                )
     */
    function getListOfToDo()
    {
        $res = $this->http->get($this->listoftodo_url);
        if (PEAR::isError($res)) {
            return $res;
        }
        if (PEAR::isError($response = $this->http->currentResponse())) {
            return $response;
        }
        return $this->_parseListOfToDo(mb_convert_encoding($response['body'], 'utf-8', 'euc-jp'));
    }
    
    /**
     * parse list of todo
     *
     * @param   string $html html
     * @return  array list of todo
     */
    function _parseListOfToDo($html)
    {
        $result = array();
        foreach ($this->_parseListOfToDoSection($this->_choiseMyListHTML($html)) as $value) {
            $value['shared'] = false;
            $result[] = $value;
        }
        foreach ($this->_parseListOfToDoSection($this->_choiseSharedListHTML($html)) as $value) {
            $value['shared'] = true;
            $result[] = $value;
        }
        return $result;
    }
    
    /**
     * choise my list section
     *
     * @param   string $html html
     * @return  string my list html
     */
    function _choiseMyListHTML($html)
    {
        $res = preg_match('/あなたのリスト(.+)<li class="add">/is', $html, $match);
        return $res ? $match[1] : '';
    }
    
    /**
     * choise shared list section
     *
     * @param   string $html html
     * @return  string my list html
     */
    function _choiseSharedListHTML($html)
    {
        $res = preg_match('/他の人のリスト(.+)id="rightside"/is', $html, $match);
        return $res ? $match[1] : '';
    }
    
    /**
     * parse list of todo section
     *
     * @param   string $html html
     * @return  array list of todo
     */
    function _parseListOfToDoSection($html)
    {
        preg_match_all('!<a[^>]*?href="(/\?mode=pjt&act=detail&id=([0-9]+))"[^>]*?>(.+?)</a>.*?<span style="color:#BFBFBF">- ([0-9]+)個!is', $html, $match, PREG_SET_ORDER);
        $result = array();
        foreach ($match as $match_value) {
            $result[] = array(
              'title'  => trim($match_value[3]),
              'url'    => 'http://' . $this->checkpad_domain . trim($match_value[1]),
              'id'     => (int)trim($match_value[2]),
              'left'   => (int)trim($match_value[4])
            );
        }
        return $result;
    }
    
    /**
     * add list of todo
     *
     * @param   string $title
     * @return  int    id
     */
    function addListOfToDo($title)
    {
        $param = array(
          'ttl' => mb_convert_encoding($title, 'euc-jp', 'utf-8'),
        );
        $res = $this->http->post($this->addlistoftodo_url, $param);
        if (PEAR::isError($res)) {
            return $res;
        }
        if (PEAR::isError($response = $this->http->currentResponse())) {
            return $response;
        }
        $listid = $this->_getListIdInToDo(mb_convert_encoding($response['body'], 'utf-8', 'euc-jp'));
        return $listid;
    }
    
    /**
     * getListIdInToDo
     *
     * @param  string $html html
     * @return int    list id
     */
    function _getListIdInToDo($html)
    {
        $res = preg_match('!/\?mode=pjt&act=memo_edit&id=([0-9]+)!is', $html, $match);
        if (!$res) {
            return PEAR::raiseError('failed find list id');
        }
        return (int)$match[1];
    }
    
    /**
     * del list of todo
     *
     * @param   int   $list_id
     * @return  void
     */
    function delListOfToDo($list_id)
    {
        $url = sprintf(
          $this->dellistoftodo_url
          , $list_id
        );
        $res = $this->http->get($url);
        if (PEAR::isError($res)) {
            return $res;
        }
        if (PEAR::isError($response = $this->http->currentResponse())) {
            return $response;
        }
    }
    
    /**
     * edit list of todo title
     *
     * @param   int    $list_id list id
     * @param   string $title
     * @return  void
     */
    function editListOfToDoTitle($list_id, $title)
    {
        $url = sprintf(
          $this->editlistoftodotitle_url
          , $list_id
        );
        $param = array(
          'ttl' => mb_convert_encoding($title, 'euc-jp', 'utf-8')
        );
        $res = $this->http->post($url, $param);
        if (PEAR::isError($res)) {
            return $res;
        }
        if (PEAR::isError($response = $this->http->currentResponse())) {
            return $response;
        }
    }
    
    /**
    /**
     * edit list of todo memo
     *
     * @param   int    $list_id list id
     * @param   string $memo
     * @return  void
     */
    function editListOfToDoMemo($list_id, $memo)
    {
        $url = sprintf(
          $this->editlistoftodomemo_url
          , $list_id
        );
        $param = array(
          'memo' => mb_convert_encoding($memo, 'euc-jp', 'utf-8'),
        );
        $res = $this->http->post($url, $param);
        if (PEAR::isError($res)) {
            return $res;
        }
        if (PEAR::isError($response = $this->http->currentResponse())) {
            return $response;
        }
    }
    
    /**
     * get todo list
     *
     * @param   string $list_id list id
     * @return  array  list of todo
     *                 array(
     *                   array(
     *                     'title'       =>string  title,
     *                     'id'          => int     detail id,
     *                     'done'        => boolean done,
     *                   ),
     *                   ...
     *                 )
     */
    function getToDoList($list_id)
    {
        $res = $this->http->get($this->getToDoListURL($list_id));
        if (PEAR::isError($res)) {
            return $res;
        }
        if (PEAR::isError($response = $this->http->currentResponse())) {
            return $response;
        }
        $html = mb_convert_encoding($response['body'], 'utf-8', 'euc-jp');
        $result = array();
        foreach ($this->_parseToDoListNotYet($html) as $value) {
            $value['done'] = false;
            $result[] = $value;
        }
        foreach ($this->_parseToDoListDone($html) as $value) {
            $value['done'] = true;
            $result[] = $value;
        }
        return $result;
    }
    
    /**
     * getToDoListURL
     *
     * @param   int    $list_id list id
     * @return  string url
     */
    function getToDoListURL($list_id)
    {
         return sprintf(
           $this->todo_url
           , $list_id
         );
    }
    
    /**
     * get todo list notyet
     *
     * @param   string $list_id list id
     * @return  array  list of todo
     *                 array(
     *                   array(
     *                     'title'  => string  title,
     *                     'id'     => int     detail id,
     *                   ),
     *                   ...
     *                 )
     */
    function getToDoListNotYet($list_id)
    {
        $res = $this->http->get($this->getToDoListURL($list_id));
        if (PEAR::isError($res)) {
            return $res;
        }
        if (PEAR::isError($response = $this->http->currentResponse())) {
            return $response;
        }
        $html = mb_convert_encoding($response['body'], 'utf-8', 'euc-jp');
        return $this->_parseToDoListNotYet($html);
    }
    
    /**
     * get todo list done
     *
     * @param   string $list_id list id
     * @return  array  list of todo
     *                 array(
     *                   array(
     *                     'title'       => string  title,
     *                     'id'          => int     detail id,
     *                   ),
     *                   ...
     *                 )
     */
    function getToDoListDone($list_id)
    {
        $res = $this->http->get($this->getToDoListURL($list_id));
        if (PEAR::isError($res)) {
            return $res;
        }
        if (PEAR::isError($response = $this->http->currentResponse())) {
            return $response;
        }
        $html = mb_convert_encoding($response['body'], 'utf-8', 'euc-jp');
        return $this->_parseToDoListDone($html);
    }
    
    /**
     * parse todo list notyet
     *
     * @param   string $html html
     * @return  array
     */
    function _parseToDoListNotYet($html)
    {
        $result = array();
        preg_match_all('!<input[^>]*?(id="ms_([0-9]+)_edit"[^>]*?|name="ttl"[^>]*?|value="([^"]*?)"[^>]*?){3}"!is', $html, $match, PREG_SET_ORDER);
        $result = array();
        foreach ($match as $match_value) {
            $result[] = array(
              'title'  => trim($match_value[3]),
              'id'     => (int)trim($match_value[2])
            );
        }
        return $result;
    }
    
    /**
     * parse todo list done
     *
     * @param   string $html html
     * @return  array
     */
    function _parseToDoListDone($html)
    {
        $result = array();
        preg_match_all('!<div id="ms_done_([0-9]+)">.+?<div id="[0-9]+" style="display:inline">(.+?)</div>!is', $html, $match, PREG_SET_ORDER);
        $result = array();
        foreach ($match as $match_value) {
            $result[] = array(
              'title'       => trim($match_value[2]),
              'id'          => (int)trim($match_value[1])
            );
        }
        return $result;
    }
    
    /**
     * add todo
     *
     * @param   int    $list_id list_id
     * @param   string $title   title
     * @return  int    id       new todo id
     */
    function addToDo($list_id, $title)
    {
        $param = array(
          'mode' => 'ms',
          'act'  => 'add',
          'ajax' => '1',
          'pjt_id' => $list_id,
          'ttl' => $title
        );
        $res = $this->http->post($this->addtodo_url, $param);
        if (PEAR::isError($res)) {
            return $res;
        }
        if (PEAR::isError($response = $this->http->currentResponse())) {
            return $response;
        }
        $todolist = $this->_parseToDoListNotYet(mb_convert_encoding($response['body'], 'utf-8', 'euc-jp'));
        return $todolist[count($todolist) -1]['id'];
    }

    /**
     * edit todo
     *
     * @param   int    $todo_id todo id
     * @param   string $title   title
     * @return  void
     */
    function editToDo($todo_id, $title)
    {
        $param = array(
          'mode' => 'ms',
          'act'  => 'edit',
          'id'   => $todo_id,
          'ttl'  => $title
        );
        $res = $this->http->post($this->edittodo_url, $param);
        if (PEAR::isError($res)) {
            return $res;
        }
        if (PEAR::isError($response = $this->http->currentResponse())) {
            return $response;
        }
    }

    /**
     * finish todo
     *
     * @param   int     $todo_id todo id
     * @return  void
     */
    function finishToDo($todo_id)
    {
        $param = array(
          'mode' => 'ms',
          'act'  => 'finish',
          'id'   => $todo_id
        );
        $res = $this->http->post($this->finishtodo_url, $param);
        if (PEAR::isError($res)) {
            return $res;
        }
        if (PEAR::isError($response = $this->http->currentResponse())) {
            return $response;
        }
    }

    /**
     * unfinish todo
     *
     * @param   int     $todo_id todo id
     * @return  void
     */
    function unfinishToDo($todo_id)
    {
        $param = array(
          'mode' => 'ms',
          'act'  => 'unfinish',
          'id'   => $todo_id
        );
        $res = $this->http->post($this->unfinishtodo_url, $param);
        if (PEAR::isError($res)) {
            return $res;
        }
        if (PEAR::isError($response = $this->http->currentResponse())) {
            return $response;
        }
    }

    /**
     * del notyet todo
     *
     * @param   int     $todo_id todo id
     * @return  void
     */
    function delNotYetToDo($todo_id)
    {
        $param = array(
          'mode' => 'ms',
          'act'  => 'del_notyet',
          'id'   => $todo_id
        );
        $res = $this->http->post($this->delnotyettodo_url, $param);
        if (PEAR::isError($res)) {
            return $res;
        }
        if (PEAR::isError($response = $this->http->currentResponse())) {
            return $response;
        }
    }

    /**
     * del done todo
     *
     * @param   int     $todo_id todo id
     * @return  void
     */
    function delDoneToDo($todo_id)
    {
        $param = array(
          'mode' => 'ms',
          'act'  => 'del_done',
          'id'   => $todo_id
        );
        $res = $this->http->post($this->deldonetodo_url, $param);
        if (PEAR::isError($res)) {
            return $res;
        }
        if (PEAR::isError($response = $this->http->currentResponse())) {
            return $response;
        }
    }
}
?>