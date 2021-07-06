<?php

class api_module_account_shared
{
    use \Novut\API\Core;

    function setRequired($field_id, $field_name)
    {
        if (!$this->input[$field_id])
        {
            $this->set_error($field_id, $field_name);
        }
    }

}