<?php
/*
 * PIMPED APACHE-STATUS
 * 
 * view: ALL REQUESTS
 * 
 */


$oDatarenderer=new Datarenderer();
echo $oDatarenderer->renderTable('requests_all');
