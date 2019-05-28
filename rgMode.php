<?php
function echoRadioGroup($class, $dev_type, $id_off, $id_on, $id_auto)
{
    echo '<div class="'.$class.'" style="margin-left:5px;float:left">
                        <input type="radio" name="1" dev_type="'.$dev_type.'"
                               id="'.$id_off.'"><label for="'.$id_off.'">выкл</label>
                        <input type="radio" name="1" dev_type="'.$dev_type.'"
                               id="'.$id_on.'"><label for="'.$id_on.'">вкл</label>
                        <input type="radio" name="1" dev_type="'.$dev_type.'"
                               id="'.$id_auto.'"><label for="'.$id_auto.'">авто</label>
    </div>';

}
