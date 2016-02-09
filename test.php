<?php

class Test {
    protected $extension = array();
    private static $randomCache = array();

    public function __construct()
    {
        self::$randomCache["blub"]  = "blah";
    }

    public function getExtension() {
        if(!isset($this->extension["et"])) {
            $this->extension["et"] = new Extension();
            $this->extension["et"]->setOwner($this);
        }

        return $this->extension["et"];
    }

    public function __wakeup()
    {
         var_dump("Wake Up Test");
    }
}

class Extension {
    protected $owner;

    /**
     * @param mixed $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    public function __wakeup()
    {
        var_dump("Wake Up Extension");
    }
}

if(file_exists("test.txt")) {
    $blub = unserialize(file_get_contents("test.txt"));
    print_r($blub);
} else {
    $t = new Test();
    $e = $t->getExtension();

    $blub = unserialize(serialize($t));
    print_r($blub);

    file_put_contents("test.txt", serialize($t));
}
