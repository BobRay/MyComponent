<script type="text/javascript">
    function getRadioValue(formName, groupName) {
        var radioGroup = document[formName][groupName];
        for (var i=0; i<radioGroup.length; i++)  {
           if (radioGroup[i].checked)  {
           return radioGroup[i].value;
           }
        }
        return null;
    }

    function confirmSubmit() {
        val = getRadioValue('mc_form', 'doit');
        if (val == 'removeobjects') {
            return confirm("[[+confirm_remove_objects]]");
        } else if (val == 'removeobjectsandfiles') {
            return confirm("[[+confirm_remove_objects_and_files]]");
        } else {
            return true;
        }

    }

</script>



    <h3>MyComponent Actions</h3>



    <form id="mc_form" name="mc_form" method="post" action="[[~[[*id]]]]">
        <p>&nbsp;<b>[[+message]]</b></p>
        <label for="bootstrap">
        Current Project:
                    <input type="text" name="currentproject" value="[[+current_project]]" id="currentproject"/>

                </label><input type="submit" name="newproject" value="New Project">
                <br/><br />

                <label for="selectproject">

                   <select name="selectproject">
                      [[+projects]]
                   </select></label> <input type="submit" name="switchproject" value="Switch Project" id="selectproject">
                   <br />
                   <br />

        <label for="bootstrap">
            <input type="radio" name="doit" value="bootstrap" id="bootstrap"/>
            Bootstrap
        </label><br/><br />
        <label for="exportobjects"> <input type="radio" name="doit" value="exportobjects" id="exportobjects"/>
            ExportObjects</label><br/><br/>
        <label for="importobjects"> <input type="radio" name="doit" value="importobjects" id="importobjects"/>
                        ImportObjects</label><br/><br/>
        <label for="lexiconhelper"> <input type="radio" name="doit" value="lexiconhelper" id="lexiconhelper"/>
            LexiconHelper</label><br/><br/>
        <label for="checkproperties"> <input type="radio" name="doit" value="checkproperties" id="checkproperties"/>
            CheckProperties</label><br/><br/>
        <label for="build"> <input type="radio" name="doit" value="build" id="build"/>
            Build</label><br/><br/><br/><br/>
        <label for="removeobjects"> <input type="radio" name="doit" value="removeobjects" id="removeobjects"/>
            RemoveObjects</label><br/><br/>
            <label for="removeobjectsandfiles"> <input type="radio" name="doit" value="removeobjectsandfiles" id="removeobjectsandfiles"/>
                        RemoveObjects and Files</label><br/><br/><br/>

        <input type="submit" value="Submit" onclick="return confirmSubmit();">
    </form>
<br /><br/>


