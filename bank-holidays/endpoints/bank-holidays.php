<?php // Version 1.0

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;

class BankHolidays {
    private $list;
    private $currentDate;
    private $division;
    public function __construct ($division = false) {
        $jsonBankHolidays = $this->fetchBankHolidays();
        if(!$jsonBankHolidays) {
           throw new \Directus\Exception\Exception( 'Unable to retrieve list of Bank Holidays from the source.' );
        }
        $this->list = $this->tidyBankHolidays($jsonBankHolidays);
        $this->currentDate = date('Y-m-d');
        if (!$division) {
            $division = "england-and-wales";
        }
        $this->division = $division;
    }
    private function fetchBankHolidays () {
        return file_get_contents('https://www.gov.uk/bank-holidays.json');
    }
    private function tidyBankHolidays ($jsonBankHolidays) {
        $tidiedBankHolidays = [];
        $decoded = json_decode($jsonBankHolidays, true);
        foreach ($decoded as $divison => $data) {
            $eventList = $data["events"];
            foreach($eventList as $event) {
                $eventDate = $event["date"];
                $tidiedBankHolidays[$divison][] = $event;
            }
        }
        return $tidiedBankHolidays;
    }
    public function getAllDivisions () {
        return $this->list;
    }
    public function getAllByDivision ($division = false) {
        if(!$division) {
            $division = $this->division;
        }
        return $this->list[ $division ];
    }
    public function checkDate ($year = false, $month = false, $date = false) {
        $divisionHolidays = $this->list[ $this->division ];

        $foundEvents = [];
        foreach ($divisionHolidays as $event) {
            $dateData = explode("-", $event['date']);
            $eventYear = $dateData[0];
            $eventMonth = $dateData[1];
            $eventDate = $dateData[2];
            if ($year && $eventYear !== $year || $month && $eventMonth !== $month || $date && $eventDate !== $date) {
                continue;
            } 
            $foundEvents[] = $event;
        }
        if($date && count($foundEvents) === 1) {
            return $foundEvents[0];
        }
        return count($foundEvents) ? $foundEvents : false; 
    }
    public function getNext ($count = false) {
        $divisionHolidays = $this->list[ $this->division ];
        $eventList = [];
        foreach($divisionHolidays as $event) {
            if($count && count($eventList) >= $count) {
                continue;
            } elseif ( $event["date"] > $this->currentDate ) {
                $eventList[] = $event;
            }
        }
        if(count($eventList) === 1 ){
            return $eventList[0];
        }
        return $eventList;
    }
    public function getLast ($count = false) {
        $divisionHolidays = array_reverse($this->list[ $this->division ]);
        $eventList = [];
        foreach($divisionHolidays as $event) {
            if($count && count($eventList) >= $count) {
                continue;
            } elseif ( $event["date"] < $this->currentDate ) {
                $eventList[] = $event;
            }
        }
        if(count($eventList) === 1){
            return $eventList[0];
        }
        return $eventList;
    }
}

return [
    '' => [
        'method' => 'GET',
        'handler' => function (Request $request, Response $response) {
            $bankHolidays = new BankHolidays();
            return $response->withJson([
                'data' => $bankHolidays->getAllDivisions()
            ]);
        }
    ],
    '/{division}' => [
        'handler' => function ($request, $response) {
            $division = $request->getAttribute('division');
            $bankHolidays = new BankHolidays( $division );
            return $response->withJSON([
                'data' => $bankHolidays->getAllByDivision( $division )
            ]);
        }
    ],
    '/{division}/next[/{count}]' => [
        'handler' => function ($request, $response) {
            $division = $request->getAttribute('division');
            $bankHolidays = new BankHolidays( $division );
            $count = $request->getAttribute('count');
            return $response->withJSON([
                'data' => $bankHolidays->getNext( $count )
            ]);
        }
    ],
    '/{division}/last[/{count}]' => [
        'handler' => function ($request, $response) {
            $division = $request->getAttribute('division');
            $bankHolidays = new BankHolidays( $division );
            $count = $request->getAttribute('count');
            return $response->withJSON([
                'data' => $bankHolidays->getLast( $count )
            ]);
        }
    ],
    '/{division}/check[/{year}[/{month}[/{date}]]]' => [
        'handler' => function ($request, $response) {
            $division = $request->getAttribute('division');
            $bankHolidays = new BankHolidays( $division );
            $year = $request->getAttribute('year');
            $month = $request->getAttribute('month');
            $date = $request->getAttribute('date');
            if (!$year) {
                throw new \Directus\Exception\Exception( "Please enter a date following this format: [/YYYY/MM/DD], the 'MM' and 'DD' are optional, however a year is required. " );
            }
            return $response->withJSON([
                'data' => $bankHolidays->checkDate( $year, $month, $date )
            ]);
        }
    ]
];
