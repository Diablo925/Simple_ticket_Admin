<?php

class module_controller extends ctrl_module
{

		static $ok;
		
    /**
     * The 'worker' methods.
     */
	
	static function doread()
    {
		global $controller;
		$currentuser = ctrl_users::GetUserDetail();
        $formvars = $controller->GetAllControllerRequests('FORM');
        //return self::ExectuteRead($formvars['innumber']);
		if (isset($formvars['inRead'])) {
                header("location: ./?module=" . $controller->GetCurrentModule() . '&show=read&ticket='. $formvars['innumber']. '');
                exit;
            }
			 return true;
	}
	
	static function ExectuteSendTicket($Ticketstatus, $ticketid, $msg)
	{
		global $zdbh;
		global $controller;
		$currentuser = ctrl_users::GetUserDetail();
		$date = date("Y-m-d - H:i:s");
		$msg = "$date -- $msg";
		$sql = $zdbh->prepare("UPDATE x_ticket SET st_ticketanswers = :msg, st_status = :ticketstatus WHERE st_number = :number AND st_groupid = :uid");
		$sql->bindParam(':uid', $currentuser['userid']);
		$sql->bindParam(':number', $ticketid);
		$sql->bindParam(':msg', $msg);
		$sql->bindParam(':ticketstatus', $Ticketstatus);
        $sql->execute();
		
        self::$ok = true;
		return true;
	}
	
	static function getisread()
    {
        global $controller;
        $urlvars = $controller->GetAllControllerRequests('URL');
        return (isset($urlvars['show'])) && ($urlvars['show'] == "read");
    }

	static function ListSelectTicket($uid)
	{
		global $zdbh;
		global $controller;
		$currentuser = ctrl_users::GetUserDetail();
		$urlvars = $controller->GetAllControllerRequests('URL');
		$ticket = $urlvars['ticket'];
		$sql = "SELECT * FROM x_ticket WHERE st_groupid = :uid AND st_number = :number ORDER BY st_number";
        $numrows = $zdbh->prepare($sql);
        $numrows->bindParam(':uid', $currentuser['userid']);
		$numrows->bindParam(':number', $ticket);
        $numrows->execute();
        if ($numrows->fetchColumn() <> 0) {
            $sql = $zdbh->prepare($sql);
            $sql->bindParam(':uid', $currentuser['userid']);
			$sql->bindParam(':number', $ticket);
            $res = array();
            $sql->execute();
            while ($row = $sql->fetch()) {
                array_push($res, array('Ticket_number' => $row['st_number'], 'Ticket_domain' => $row['st_domain'],
										'Ticket_subject' => $row['st_subject'], 'Ticket_msg' => $row['st_meassge'], 'Ticket_Answers' => $row['st_ticketanswers']));
            }
            return $res;
        } else {
            return false;
        }
		
	}
	
	static function getTicketstatus()
	{
		global $zdbh;
		global $controller;
		$currentuser = ctrl_users::GetUserDetail();
		$urlvars = $controller->GetAllControllerRequests('URL');
		$ticket = $urlvars['ticket'];
		$sql = "SELECT * FROM x_ticket WHERE st_groupid = :uid AND st_number = :number";
		$sql = $zdbh->prepare($sql);
            $sql->bindParam(':uid', $currentuser['userid']);
			$sql->bindParam(':number', $ticket);
            $res = array();
            $sql->execute();
            while ($row = $sql->fetch()) {
				if($row["st_status"] == "Open") { $statusopen = "selected"; }
				if($row["st_status"] == "Re-Open") { $statusreopen = "selected"; }
				if($row["st_status"] == "Close") { $statusclose = "selected"; }
				if($row["st_status"] == "Pending") { $statuspending = "selected"; }
				$res = '<option value="Open" '.$statusopen.'>Open</option><option value="Re-Open" '.$statusreopen.'>Re-Open</option><option value="Pending" '.$statuspending.'>Pending</option><option value="Close" '.$statusclose.'>Close</option>';
			}
			return $res;
	}
	
	static function ListTicket($uid)
    {
		global $zdbh;
		global $controller;
		$currentuser = ctrl_users::GetUserDetail();
		$sql = "SELECT * FROM x_ticket WHERE st_groupid = :uid";
        $numrows = $zdbh->prepare($sql);
        $numrows->bindParam(':uid', $currentuser['userid']);
        $numrows->execute();
        if ($numrows->fetchColumn() <> 0) {
		$sql = $zdbh->prepare($sql);
            $sql->bindParam(':uid', $currentuser['userid']);
            $res = array();
            $sql->execute();
            while ($row = $sql->fetch()) {
                array_push($res, array('ticketid' => $row['st_id'], 'ticketnumber' => $row['st_number'], 'ticketdomain' => $row['st_domain'], 'ticketsubject' => $row['st_subject'], 'ticketstatus' => $row['st_status']));
            }
            return $res;
        } else {
            return false;
        }
	} 
	
    /**
     * End 'worker' methods.
     */

    /**
     * Webinterface sudo methods.
     */

	static function getTicket()
    {
        global $controller;
        $currentuser = ctrl_users::GetUserDetail();
        return self::ListSelectTicket($currentuser['userid']);
    }
	
	static function getTicketList()
    {
        global $controller;
		
        $currentuser = ctrl_users::GetUserDetail();
        return self::ListTicket($currentuser['userid']);
    } 
	
	static function dosend()
    {
        global $controller;
        $formvars = $controller->GetAllControllerRequests('FORM');
        if (self::ExectuteSendTicket($formvars['inStatus'], $formvars['innumber'], $formvars['inMessage']));
	}
	
	static function getResult()
    {
		 if (self::$ok) {
            return ui_sysmessage::shout(ui_language::translate("The ticket is updatet!!"), "zannounceok");
        }
        return;
    }

    /**
     * Webinterface sudo methods.
     */
}
?>