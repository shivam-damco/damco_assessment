<?php
/**
 * Copyright (C) 2011-2013 Graham Breach
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * For more information, please contact <graham@goat1000.com>
 */

require_once 'SVGGraphMultiGraph.php';
require_once 'SVGGraphHorizontalBarGraph.php';

class HorizontalGroupedBarGraph extends HorizontalBarGraph {

  protected $multi_graph;
  protected $legend_reverse = true;

  protected function Draw()
  {
    $body = $this->Grid() . $this->Guidelines(SVGG_GUIDELINE_BELOW);

    $chunk_count = count($this->multi_graph);
    $gap_count = $chunk_count - 1;
    $bar_height = ($this->bar_space >= $this->bar_unit_height ? '1' : 
      $this->bar_unit_height - $this->bar_space);
    $chunk_gap = $gap_count > 0 ? $this->group_space : 0;
    if($gap_count > 0 && $chunk_gap * $gap_count > $bar_height - $chunk_count)
      $chunk_gap = ($bar_height - $chunk_count) / $gap_count;
    $chunk_height = ($bar_height - ($chunk_gap * ($chunk_count - 1)))
      / $chunk_count;
    $chunk_unit_height = $chunk_height + $chunk_gap;
    $bar_style = array();
    $bar = array('height' => $chunk_height);

    $bnum = 0;
    $bspace = $this->bar_space / 2;
    $ccount = count($this->colours);
    $bars_shown = array_fill(0, $chunk_count, 0);
		
		// Tanuj => New code added		
		$regionalArr = array();
		$nationalArr = array();
		if(count($this->multi_graph->values->data) > 0){
			foreach($this->multi_graph->values->data as $chartData){
				if(strtolower($chartData[0]) == 'regional'){
					$regionalArr = array($chartData[1], $chartData[2], $chartData[3]);
				}elseif(strtolower($chartData[0]) == 'national'){
					$nationalArr = array($chartData[1], $chartData[2], $chartData[3]);
				}
			}
		}
		// xxxxx Tanuj => New code ends here xxxxx
		  
    foreach($this->multi_graph as $itemlist) {
      $k = $itemlist[0]->key;
      $bar_pos = $this->GridPosition($k, $bnum);

      if(!is_null($bar_pos)) {
        for($j = 0; $j < $chunk_count; ++$j) {
          $bar['y'] = $bar_pos - $bspace - $bar_height +
            (($chunk_count - 1 - $j) * $chunk_unit_height);
          $item = $itemlist[$j];
          $this->SetStroke($bar_style, $item, $j);
		  
		  // Tanuj
		  // $bar_style['fill'] = $this->GetColour($item, $j % $ccount);	
			if(strtolower($k) == 'national'){
				$bar_style['fill'] = '#666666';
			}else{
				if(strtolower($k) == 'regional'){
					if($item->value > $nationalArr[$j])
						$bar_style['fill'] = 'green';	
					elseif($item->value == $nationalArr[$j])
						$bar_style['fill'] = '#F8C957';		
					else
						$bar_style['fill'] = 'red';	
				}else{
					if($item->value > $regionalArr[$j])
						$bar_style['fill'] = 'green';	
					elseif($item->value == $regionalArr[$j])
						$bar_style['fill'] = '#F8C957';		
					else
						$bar_style['fill'] = 'red';		
				}
			}
		  // xxxxx Tanuj changes ends here xxxxx
		  
		  
          $this->Bar($item->value, $bar);

          if($bar['width'] > 0) {
            ++$bars_shown[$j];

            if($this->show_tooltips)
              $this->SetTooltip($bar, $item, $item->value, null,
                !$this->compat_events && $this->show_bar_labels);
            $rect = $this->Element('rect', $bar, $bar_style);
            if($this->show_bar_labels)
              $rect .= $this->BarLabel($item, $bar);
            $body .= $this->GetLink($item, $k, $rect);
            unset($bar['id']); // clear ID for next generated value
          }
          $this->bar_styles[$j] = $bar_style;
        }
      }
      ++$bnum;
    }
    if(!$this->legend_show_empty) {
      foreach($bars_shown as $j => $bar) {
        if(!$bar)
          $this->bar_styles[$j] = NULL;
      }
    }

    $body .= $this->Guidelines(SVGG_GUIDELINE_ABOVE) . $this->Axes();
    return $body;
  }

  /**
   * construct multigraph
   */
  public function Values($values)
  {
    parent::Values($values);
    if(!$this->values->error)
      $this->multi_graph = new MultiGraph($this->values, $this->force_assoc,
        $this->require_integer_keys);
  }

  /**
   * Find the longest data set
   */
  protected function GetHorizontalCount()
  {
    return $this->multi_graph->ItemsCount(-1);
  }

  /**
   * Returns the maximum (stacked) value
   */
  protected function GetMaxValue()
  {
    return $this->multi_graph->GetMaxValue();
  }

  /**
   * Returns the minimum (stacked) value
   */
  protected function GetMinValue()
  {
    return $this->multi_graph->GetMinValue();
  }

  /**
   * Returns the key from the MultiGraph
   */
  protected function GetKey($index)
  {
    return $this->multi_graph->GetKey($index);
  }

  /**
   * Returns the maximum key from the MultiGraph
   */
  protected function GetMaxKey()
  {
    return $this->multi_graph->GetMaxKey();
  }

  /**
   * Returns the minimum key from the MultiGraph
   */
  protected function GetMinKey()
  {
    return $this->multi_graph->GetMinKey();
  }

}

