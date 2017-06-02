<?php
/**
 * TNH Framework
 *
 * A simple PHP framework created using the concept of codeigniter with bootstrap twitter
 *
 * This content is released under the GNU GPL License (GPL)
 *
 * Copyright (C) 2017 Tony NGUEREZA
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/


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
