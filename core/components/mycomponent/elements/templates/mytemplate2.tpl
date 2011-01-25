<!-- Example Template  -->

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">


<head>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
    
    <base href="http://localhost/modx/" />
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


    <div class="datepicker">
        <label for="createdon" title="The date the resource was originally created.">Created On:</label>
        <div class = "np_date_hints"><span class = "np_date_hint"> (Y-M-D)</span><span class ="np_time_hint">(Time - any format)</span></div>

        <input type="text" class="w4em format-y-m-d divider-dash no-transparency" id="createdon" name="createdon" maxlength="10" readonly="readonly" value="" />
        <input type="text" class="createdon_time" name="createdon_time" id="createdon_time" value="" />
    </div>

    <div class="datepicker">
        <label for="publishedon" title="The date the resource was published.">Published On:</label>
        <div class = "np_date_hints"><span class = "np_date_hint"> (Y-M-D)</span><span class ="np_time_hint">(Time - any format)</span></div>

        <input type="text" class="w4em format-y-m-d divider-dash no-transparency" id="publishedon" name="publishedon" maxlength="10" readonly="readonly" value="" />
        <input type="text" class="publishedon_time" name="publishedon_time" id="publishedon_time" value="" />
    </div>

    <div class="datepicker">
        <label for="editedon" title="The date the resource was most recently edited.">Edited On:</label>
        <div class = "np_date_hints"><span class = "np_date_hint"> (Y-M-D)</span><span class ="np_time_hint">(Time - any format)</span></div>

        <input type="text" class="w4em format-y-m-d divider-dash no-transparency" id="editedon" name="editedon" maxlength="10" readonly="readonly" value="" />
        <input type="text" class="editedon_time" name="editedon_time" id="editedon_time" value="" />
    </div>



<fieldset class="np-tv-listbox" title=""><legend>List1</legend>
<select name="List1" size="4">

    <option value="Yes">Yes</option>
    <option value="No">No</option>

    <option value="Parent">Parent</option>
    <option value="System Default" selected="selected" >System Default</option>
</select>
</fieldset>

            <label for="pagetitle" title="The name/title of the resource. Try to avoid using backslashes in the name!">Title: </label>
            <input name="pagetitle" class="text" id="pagetitle" type="text"  value="" maxlength="60" />

    <div class="datepicker">

        <label for="pub_date" title=" (optional) set date to automatically publish resource. Click on the calendar icon to select a date.">Publish Date:</label>
        <div class = "np_date_hints"><span class = "np_date_hint"> (Y-M-D)</span><span class ="np_time_hint">(Time - any format)</span></div>
        <input type="text" class="w4em format-y-m-d divider-dash no-transparency" id="pub_date" name="pub_date" maxlength="10" readonly="readonly" value="" />
        <input type="text" class="pub_date_time" name="pub_date_time" id="pub_date_time" value="" />
    </div>

    <div class="datepicker">

        <label for="unpub_date" title=" (optional) set date to automatically unpublish the resource. Click on the calendar icon to select a date.">Unpublish Date:</label>
        <div class = "np_date_hints"><span class = "np_date_hint"> (Y-M-D)</span><span class ="np_time_hint">(Time - any format)</span></div>
        <input type="text" class="w4em format-y-m-d divider-dash no-transparency" id="unpub_date" name="unpub_date" maxlength="10" readonly="readonly" value="" />
        <input type="text" class="unpub_date_time" name="unpub_date_time" id="unpub_date_time" value="" />
    </div>



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




    <div class="datepicker">
        <label for="MyTv6" title="MyTv6 Description">MyTv6:</label>
        <div class = "np_date_hints"><span class = "np_date_hint"> (Y-M-D)</span><span class ="np_time_hint">(Time - any format)</span></div>
        <input type="text" class="w4em format-y-m-d divider-dash no-transparency" id="MyTv6" name="MyTv6" maxlength="10" readonly="readonly" value="12/21/2002" />
        <input type="text" class="MyTv6_time" name="MyTv6_time" id="MyTv6_time" value="02:30" />
    </div>

            <label for="longtitle" title="This is a longer title for your resource. It is handy for search engines, and might be more descriptive for the resource.">Long Title: </label>
            <input name="longtitle" class="text" id="longtitle" type="text"  value="" maxlength="60" />
<fieldset class="np-tv-checkbox" title="When enabled, the resource is able to be searched. This setting can also be used for other purposes in your snippets."><legend>Searchable</legend>
    <input type="hidden" name = "searchable" value = "" />
    <span class="option"><input class="checkbox" type="checkbox" name="searchable" id="searchable" value="1" checked="checked"/></span>
 </fieldset>
<fieldset class="np-tv-checkbox" title="When enabled, the resource will be saved to the cache."><legend>Cacheable</legend>
    <input type="hidden" name = "cacheable" value = "" />

    <span class="option"><input class="checkbox" type="checkbox" name="cacheable" id="cacheable" value="1" checked="checked"/></span>
 </fieldset>
<fieldset class="np-tv-checkbox" title="When published the resource is available to the public immediately after saving it."><legend>Published</legend>
    <input type="hidden" name = "published" value = "" />
    <span class="option"><input class="checkbox" type="checkbox" name="published" id="published" value="1" checked="checked"/></span>
 </fieldset>
<fieldset class="np-tv-checkbox" title="When enabled the resource will *not* be available for use inside a web menu. Please note that some Menu Builders might choose to ignore this option."><legend>Hide from Menus</legend>
    <input type="hidden" name = "hidemenu" value = "" />
    <span class="option"><input class="checkbox" type="checkbox" name="hidemenu" id="hidemenu" value="1" checked="checked"/></span>

 </fieldset>

            <label for="alias" title="An alias for this resource. This will make the resource accessible using:http://yourserver/aliasNoteThis only works if you're using friendly URLs.">Resource Alias: </label>
            <input name="alias" class="text" id="alias" type="text"  value="" maxlength="60" />

            <label class="intfield" for="id" title="The resource ID of the resource.">Resource ID: </label>
            <input name="id" class="int" id="id" type="text"  value="" maxlength="3" />



<label title="My  TV5 Description">MyTv5</label>
            <div class="modx-richtext">
                <textarea rows="8" cols="60" class="modx-richtext" name="MyTv5" id="MyTv5">Hello Sailor.</textarea>
            </div>



<label for="MyTv7" title="MyTv7 Description">MyTv7</label><textarea rows="10" cols="60" name="MyTv7" id="MyTv7"></textarea>


<fieldset class="np-tv-checkbox" title=""><legend>MyTv8</legend>
    <input type="hidden" name="MyTv8[]" value = "" />
    <span class="option"><input class="checkbox" type="checkbox" name="MyTv8[]" value="Yellow" checked ="checked"  />Yellow</span>
</fieldset>

                <label for="introtext" title="A brief summary of the resource.">Summary (introtext): </label>
                <div class="modx-richtext">
                    <textarea  rows="10" cols="60" class="modx-richtext" name="introtext" id="introtext"></textarea>

                </div>

                <label for="content">Resource Content: </label>
                <div class="modx-richtext">
                    <textarea rows="10" cols="60" class="modx-richtext" name="content" id="content"></textarea>
                </div>
        <span class = "buttons">
            <input class="submit" type="submit" name="Submit" value="Submit" />

            <input type="button" class="cancel" name="Cancel" value="Cancel" onclick="window.location = 'http://localhost/gitclone/manager/?a=13&amp;id=8' " />
        </span>

    </form>
</div>

</body>
</html>