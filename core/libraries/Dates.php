<?php

	class Dates{

		public static function times($times){
			if(!is_int($times)){
				$times = strtotime($times);
			}
			$hour = date('H', $times);
			$minute = date('i', $times);
			return $hour."h".$minute;
		}

		public static function days($times){
			if(!is_int($times)){
				$times = strtotime($times);
			}
			$day = date('d', $times);
			$month = date('m', $times);
			$year = date('Y', $times);
			$hour = date('H', $times);
			$minute = date('i', $times);
			$m = null;
			switch($month){
				case 1:
					$m="Jan";
				break;
				case 2:
					$m="Fév";
				break;
				case 3:
					$m="Mar";
				break;
				case 4:
					$m="Avr";
				break;
				case 5:
					$m="Mai";
				break;
				case 6:
					$m="Juin";
				break;
				case 7:
					$m="Juil";
				break;
				case 8:
					$m="Août";
				break;
				case 9:
					$m="Sep";
				break;
				case 10:
					$m="Oct";
				break;
				case 11:
					$m="Nov";
				break;
				case 12:
					$m="Déc";
				break;
			}
			return $day.' '.$m.' '.$year.', '.self::times($times);
		}

		public static function daysOnly($times){
			if(!is_int($times)){
				$times = strtotime($times);
			}
			$day = date('d', $times);
			$month = date('m', $times);
			$year = date('Y', $times);
			$hour = date('H', $times);
			$minute = date('i', $times);
			$m = null;
			switch($month){
				case 1:
					$m="Jan";
				break;
				case 2:
					$m="Fev";
				break;
				case 3:
					$m="Mar";
				break;
				case 4:
					$m="Avr";
				break;
				case 5:
					$m="Mai";
				break;
				case 6:
					$m="Juin";
				break;
				case 7:
					$m="Juil";
				break;
				case 8:
					$m="Août";
				break;
				case 9:
					$m="Sep";
				break;
				case 10:
					$m="Oct";
				break;
				case 11:
					$m="Nov";
				break;
				case 12:
					$m="Déc";
				break;
			}
			return $day.' '.$m.' '.$year;
		}

		public static function date($times){
			if(!is_int($times)){
				$times = strtotime($times);
			}
			$diff = time() - $times;
			$res = '';
			if($diff < 60){
				$res = 'il ya '.$diff.' sec';
			}
			else if($diff >= 60 && $diff < 3600){
				$res = 'il ya '.floor($diff/60).' min';
			}
			else if($diff >= 3600 && $diff < 86400){
				$res = 'il ya '.floor($diff/3600).'h';
			}
			else if($diff >= 86400 && $diff < 129600){
				$res = 'Hier à '.self::times($times);
			}
			else{
				$res = self::days($times);
			}
			return $res;
		}

	}
