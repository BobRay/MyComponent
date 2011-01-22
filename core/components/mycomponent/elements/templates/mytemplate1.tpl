<!-- This is *not* a form template, it is an example of how checkboxes, radio options, and listboxes are rendered  -->

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">


<head>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
    <base href="http://localhost/gitclone/" />
    <link rel="stylesheet" href="/gitclone/assets/components/newspublisher/assets/components/newspublisher/datepicker/css/datepicker.css" type="text/css" />
    <script type="text/javascript" src="/gitclone/assets/components/newspublisher/assets/components/newspublisher/datepicker/js/datepicker.js"></script>
    <link rel="stylesheet" href="/gitclone/assets/components/newspublisher/assets/components/newspublisher/css/newspublisher.css" type="text/css" />



    <title>MyPage</title>
</head>
<body>
<div class="newspublisher">
        <h2>Create/Edit Resource</h2>




        <form action="newspublisher/" method="post">
            <input name="hidSubmit" type="hidden" id="hidSubmit" value="true" />

<fieldset class="np-tv-listbox" title=""><legend>List1</legend>
<select name="List1" size="4">

    <option value="Yes">Yes</option>
    <option value="No">No</option>

    <option value="Parent">Parent</option>
    <option value="System Default" selected="selected" >System Default</option>
</select>
</fieldset>

<fieldset class="np-tv-checkbox" title="My TV1 Description"><legend>MyTv1</legend>
    <input type="hidden" name="MyTv1[]" value = "" />
    <span class="option"><input class="checkbox" type="checkbox" name="MyTv1[]" value="Red" />Red</span>
    <span class="option"><input class="checkbox" type="checkbox" name="MyTv1[]" value="Blue" checked ="checked"  />Blue</span>
    <span class="option"><input class="checkbox" type="checkbox" name="MyTv1[]" value="Green" checked ="checked"  />Green</span>
</fieldset>


<fieldset class="np-tv-radio" title="MyTv2 Description"><legend>MyTv2Caption</legend>
    <span class="option"><input class="radio" type="radio" name="MyTv2" value="redish" checked ="checked"  />redish</span>
    <span class="option"><input class="radio" type="radio" name="MyTv2" value="blueish" />blueish</span>
    <span class="option"><input class="radio" type="radio" name="MyTv2" value="greenish" />greenish</span>
</fieldset>



<fieldset class="np-tv-listbox" title="MyTv3 Description"><legend>MyTv3</legend>
<select name="MyTv3" size="3">
    <option value="Red" selected="selected" >Red</option>
    <option value="Blue">Blue</option>
    <option value="Green">Green</option>
</select>
</fieldset>



<fieldset class="np-tv-listbox-multiple" title="MyTv4 Description"><legend>MyTv4</legend>
<select name="MyTv4[]"  multiple="multiple" size="3">
    <option value="Red" selected="selected" >Red</option>
    <option value="Blue">Blue</option>
    <option value="Green" selected="selected" >Green</option>
</select>
</fieldset>

<fieldset class="np-tv-checkbox" title="When enabled, the resource is able to be searched. This setting can also be used for other purposes in your snippets."><legend>Searchable</legend>
    <input type="hidden" name = "searchable" value = "" />
    <span class="option"><input class="checkbox" type="checkbox" name="searchable" id="searchable" value="1" checked="checked"/></span>
 </fieldset>


<fieldset class="np-tv-checkbox" title=""><legend>MyTv8</legend>
    <input type="hidden" name="MyTv8[]" value = "" />
    <span class="option"><input class="checkbox" type="checkbox" name="MyTv8[]" value="Yellow" checked ="checked"  />Yellow</span>
</fieldset>

        <span class = "buttons">
            <input class="submit" type="submit" name="Submit" value="Submit" />

            <input type="button" class="cancel" name="Cancel" value="Cancel" onclick="window.location = 'http://localhost/gitclone/manager/?a=13&amp;id=8' " />
        </span>

    </form>
</div>

</body>
</html>