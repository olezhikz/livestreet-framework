<div class="d-flex justify-content-center mt-4">
    {component "button" 
        icon    = [ icon=>"vk", display=>"b", classes=>"mr-1 "]
        attributes = ['style' => "background-color:#527396;"]
        classes = "border-0 mx-1 px-3"
        bmods   = "primary"
        url     = {router page="sociality/login/Vkontakte"}
        text    = "ВКонтакте"}

    {component "button" 
        icon    = [ icon=>"facebook-f", display=>"b", attributes => ['style' => "color:#495ba7;"], classes=>"bg-white px-1 mr-1"]
        attributes = ['style' => "background-color:#495ba7;"]
        classes = "border-0 mx-1 px-3"
        bmods   = "primary"
        url     = {router page="sociality/login/Facebook"}
        text    = "Facebook"}
</div>