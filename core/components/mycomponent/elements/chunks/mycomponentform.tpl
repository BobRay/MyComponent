<script type="text/javascript" src="[[+mc.jsFile]]"></script>
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

    function switchProject(project) {
        $('#selectproject').val(project);
        $('#switchproject').submit();
    }

    function doAction(action){
        $('#doit').val(action);
        $('#mc_action').submit();
    }

</script>

<form id="switchproject" method="post" action="[[~[[*id]]]]">
    <input type="hidden" name="currentproject" value="[[+current_project]]" id="currentproject"/>
    <input type="hidden" name="selectproject" id="selectproject" />
    <input type="hidden" name="switchproject" id="switchproject" value="Switch Project" />
</form>

<form id="mc_action" method="post" action="[[~[[*id]]]]">
    <input type="hidden" name="currentproject" value="[[+current_project]]" id="currentproject"/>
    <input type="hidden" name="doit" id="doit"/>
</form>

<div class="row">
    <div class="small-12 columns">
        <h1>MyComponent Project: [[+current_project]]</h1>
    </div>

    <div class="projects small-12 medium-6 columns columns1">
        <h2>Projects</h2>
        [[+mc.projects]]
    </div>

    <div class="actions small-12 medium-6 columns columns1">
        <h2>MyComponent Actions</h2>

        <a href="javascript:doAction('bootstrap')" class="action small-12 columns" data-action="bootstrap">Bootstrap</a>
        <a href="javascript:doAction('exportobjects')" class="action small-12 columns" data-action="exportobjects">ExportObjects</a>
        <a href="javascript:doAction('importobjects')" class="action small-12 columns" data-action="importobjects">ImportObjects</a>
        <a href="javascript:doAction('lexiconhelper')" class="action small-12 columns" data-action="lexiconhelper">LexiconHelper</a>
        <a href="javascript:doAction('checkproperties')" class="action small-12 columns" data-action="checkproperties">CheckProperties</a>
        <a href="javascript:doAction('build')" class="action small-12 columns" data-action="build">Build</a>
        <a href="javascript:doAction('removeobjects')" class="action small-12 columns" data-action="removeobjects">RemoveObjects</a>
        <a href="javascript:doAction('removeobjectsandfiles')" class="action small-12 columns" data-action="removeobjectsandfiles">RemoveObjects and Files</a>


        <form id="mc_form" name="mc_form" method="post" action="[[~[[*id]]]]">
            <label for="bootstrap">
                <input type="text" name="currentproject" value="NewProject" id="currentproject"/>

            </label><input type="submit" name="newproject" value="New Project">
        </form>
    </div>

    <div class="logs small-12 columns">
        <h2>Log</h2>
        <p>&nbsp;<b>[[+message]]</b></p>

        <!--Messages from MyComponent, if any-->
        <pre>
            [[+mc.logs]]
        </pre>
    </div>
</div>

<script type="text/javascript">
    /*Keeps columns equal height*/
    //Modified from http://www.cssnewbie.com/equal-height-columns-with-jquery/#.UtX2EJ5dXTc
    function equalHeight(group) {

        group.each(function(){
            $(this).css('height', 'auto');
        })

        if ($(document).width() > 600) {
            var tallest = 0;
            group.each(function() {
                var thisHeight = $(this).height();
                if(thisHeight > tallest) {
                    tallest = thisHeight;
                }
            });
            group.css('height', tallest + 'px');
        } else {
            group.each(function(){
                $(this).css('height', 'auto');
            })
        }
    }

    $(document).ready(function(){
        /*Keeps columns on homepage equal height*/
        equalHeight($('.columns1'));
        $(window).resize(function(){equalHeight($('.columns1'));});
    });

</script>
