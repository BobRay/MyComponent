<div style="margin:100px;padding:100px">

    <h3>MyComponent Actions</h3>

    <form method="post" action="[[~[[*id]]]]">

        <label for="bootstrap">
            <input type="radio" checked="checked" name="doit" value="bootstrap" id="bootstrap"/>
            Bootstrap
        </label><br/><br />
        <label for="exportobjects"> <input type="radio" name="doit" value="exportobjects" id="exportobjects"/>
            ExportObjects</label><br/><br/>
        <label for="lexiconhelper"> <input type="radio" name="doit" value="lexiconhelper" id="lexiconhelper"/>
            LexiconHelper</label><br/><br/>
        <label for="build"> <input type="radio" name="doit" value="build" id="build"/>
            Build</label><br/><br/><br/><br/>
        <label for="removeobjects"> <input type="radio" name="doit" value="removeobjects" id="removeobjects"/>
            RemoveObjects</label><br/><br/><br/>

        

        <input type="submit" value="Submit">
    </form>


</div>