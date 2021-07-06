<?php

class api_module_account_preset extends api_module_account_shared
{
    function post_index()
    {
        $this->setRequired('WUserID', 'Unknown WUser');
        $this->setRequired('Token', 'Unknown Token');
        $this->error_display();

        $WUser = wusers()->get($this->input['WUserID']);

        if ($WUser->getWUserID())
        {
            $notification = new \Novut\Tools\Notification('account.enduser.preset');
            $notification->setBodyTemplate(true);
            $notification->toWUser($WUser->getWUserID(), ['WUserInfo' => $WUser->export()]);
            $notification->addParam('WUserInfo', $WUser->export());
            $notification->addParam('Token', $this->input['Token']);
            $notification->addParam('WebBaseUrl', \BN_Var::$Config['Misc']['web']['url']);
            $notification->send();

        }
        else
        {
            $this->error('Unknown user');
        }

        $this->success();

    }
}
(new api_module_account_preset)->init();