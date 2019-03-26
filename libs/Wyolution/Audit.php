<?php 
namespace Wyolution;

/**
 * Audit functions web application functions
 * 
 * @package Wyolution
 * @author Mark Thoney <mark@wyolution.com>
 *
 */
class Audit {
	
	/**
     * log event
     *
     * @return void
     * @param info info details
     */
    public static function log($info,$username='') {
    	$f3 = \Base::instance();
    	$db = $f3->get("DB");
    	$audit =new \Wyolution\Mapper($db,'auditlogs');
    	if ($username == '')
    		$audit->username = $f3->get('SESSION.user.username');
    	else
    		$audit->username = $username;
   		$audit->ip = $f3->get('SERVER.REMOTE_ADDR');
   		$audit->info = $info;
   		$audit->save();
    }
}
