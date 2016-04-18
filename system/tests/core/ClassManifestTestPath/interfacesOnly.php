<?php
defined("IN_GOMA") OR die();

/******
 * Class interfacesOnly
 */
interface myinterface1 {
}

interface myinterface2




/*
 * hmm this is a tricky thing :-)
 */



{

}interface myinterface3{}interface myinterface4 extends myinterface3{

}
interface myinterface5

    extends myinterface1 {

}