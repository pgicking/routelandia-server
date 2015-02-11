<?php

class StationsTuple {

    protected $highwayid;
    protected $stationTuples;

    function __construct()
    {
        print("\nCreating StationsTuple object\n");
        $this->stationTuples = new SplDoublyLinkedList();
        var_dump($this->stationTuples);
    }

    public function debugg()
    {
        print("\n-----Start Debugg----");
        print("\nDebugg: Highwayid: ".$this->highwayid);
        print("\nDebugg: Tuples: ");
        foreach($this->stationTuples as $elem)
        {
            print_r($elem." ");
        }
        print("\n".'Debugg: $this->stationsTuple returns:'."\n");
        var_dump($this->stationTuples);
        print("-----End Debugg----\n");
    }

    /**
     * @return mixed
     */
    public function getStationTuples()
    {
        return $this->stationTuples;
    }

    /**
     * @param array $tuple
     * @internal param array $stationTuples
     */
    public function addStationTuple($tuple)
    {
        print("\nadding tuple to station\n");
        var_dump($this->stationTuples);
        $this->stationTuples->push($tuple);
    }

    public function setStationTuples($stationTuples)
    {
        $this->stationTuples = $stationTuples;
    }

    /**
     * @return mixed
     */
    public function getHighwayid()
    {
        return $this->highwayid;
    }

    /**
     * @param mixed $highwayid
     */
    public function setHighwayid($highwayid)
    {
        $this->highwayid = $highwayid;
    }



}
