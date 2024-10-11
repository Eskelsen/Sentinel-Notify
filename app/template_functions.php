<?php

# Template Functions

# Last Email filter
function mailFill($values){
    $mail_content = file_get_contents(NLS . 'template.html');
    $vars = ['[title]','[logo]','[headline]','[header]','[description]','[content]','[year]','[observation]','[linkhash]','[color]','[mark]'];
    return str_replace($vars,$values,$mail_content);
}
