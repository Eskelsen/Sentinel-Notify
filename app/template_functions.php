<?php

# Template Functions

# Last Email filter
function mailFill($values,$template){
    $mail_content = file_get_contents(TMPLTS . $template);
    $vars = ['[title]','[logo]','[headline]','[header]','[description]','[content]','[year]','[observation]','[linkhash]','[color]','[mark]'];
    return str_replace($vars,$values,$mail_content);
}
