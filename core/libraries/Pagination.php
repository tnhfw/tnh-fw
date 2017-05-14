<?php
    class Pagination{

        public function __construct(){

        }

        public function getLink($total, $current_page_no, $nb_link = 10, $nb_per_page = 10){
            $queryString = Url::queryString();
            $current = Url::current();
            if($queryString == ''){
                $query = '?page=';
             }
            else{
                $tab = explode('page=', $queryString);
                $nb = count($tab);
                if($nb == 1){
                    $query = '?'.$queryString.'&page=';
                 }
                else{
                    if($tab[0] == ''){
                        $query = '?page=';
                    }
                    else{
                        $query = '?'.$tab[0].'page=';
                    }
                }
            }
            $temp = explode('?', $current);
            $query = $temp[0].$query;

            $navbar = '';
            $nb_page = ceil($total/$nb_per_page);
            if($nb_page <= 1 || $nb_link <= 0 || $nb_per_page <= 0 ||
                $current_page_no <= 0 || !is_numeric($nb_link) || !is_numeric($nb_per_page)
            ){
                return $navbar;
            }
            if($nb_link % 2 == 0){
                $start = $current_page_no - ($nb_link/2) + 1;
                $end = $current_page_no + ($nb_link/2);
            }
            else{
                $start = $current_page_no - floor($nb_link/2);
                $end = $current_page_no + floor($nb_link/2);
            }
            if($start <= 1){
                $begin = 1;
                $end = $nb_link;
            }
            else if($start > 1 && $end < $nb_page){
                $begin = $start;
                $end = $end;
            }
            else{
                $begin = ($nb_page-$nb_link) + 1;
                $end = $nb_page;
            }
            if($nb_page <= $nb_link){
                $begin = 1;
                $end = $nb_page;
            }
            if($current_page_no == 1){
                for($i = $begin; $i <= $end; $i++){
                    if($i == $current_page_no)
                        $navbar .= '<li class = "active"><a href = "#">'.$current_page_no."</a></li>";
                    else
                        $navbar .= "<li><a href='$query".$i."'>$i</a></li>";
                }
            $navbar .= "<li><a href='$query".($current_page_no+1)."'>Suiv &raquo;</a></li>";
            }
            else if($current_page_no > 1 && $current_page_no < $nb_page){
                $navbar .= "<li><a href='$query".($current_page_no-1)."'>&laquo; Préc</a></li>";
                for($i = $begin; $i <= $end; $i++){
                    if($i == $current_page_no)
                        $navbar .= '<li class = "active"><a href = "#">'.$current_page_no.'</a></li>';
                    else
                        $navbar .= "<li><a href='$query".$i."'>$i</a></li>";
                }
            $navbar .= "<li><a href='$query".($current_page_no+1)."'>Suiv &raquo;</a></li>";
            }
            else if($current_page_no == $nb_page){
                $navbar .= "<li><a href='$query".($current_page_no-1)."'>&laquo; Préc</a></li>";
                for($i = $begin; $i <= $end; $i++){
                    if($i == $current_page_no)
                        $navbar .= '<li class = "active"><a href = "#">'.$current_page_no.'</a></li>';
                    else
                        $navbar .= "<li><a href='$query".$i."'>$i</a></li>";
                }
            }
            $navbar = '<ul class = "pagination">'.$navbar.'</ul>';
            return $navbar;
        }
    }
