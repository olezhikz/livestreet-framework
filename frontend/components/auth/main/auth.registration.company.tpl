
<form action="{router page='auth/register'}" method="post" name="register_company" 
      autocomplete="off"
      data-url="{router page='auth/ajax-register'}" data-form-ajax data-form-validate class="" novalidate>
    
    <div class="mx-3">
    <div class="row bg-light my-2 pt-3">
        
        {$oUserProfile = Engine::GetEntity('User_User')}
        {$oUserProfile->_setValidateScenario('registration')}

        {hook run='form_registration_begin'}
        
        <div class="col-lg-3 col-sm-6">
        {* E-mail *}
        {component 'form' 
            template    = 'text' 
            name        = "mail"
            placeholder = $aLang.auth.registration.form.fields.email.placeholder
            type        = "email"
            validate    = [ 
                entity  => $oUserProfile,
                remote  => true
            ]}
        </div>
        
        <div class="col-lg-3 col-sm-6">
        {* Имя Фамилия *}
        {component 'form' 
            template    = 'text' 
            name        = "name"
            placeholder = $aLang.auth.registration.form.fields.name_company.placeholder
            type        = "text"
            validate    = [ 
                entity  => $oUserProfile
            ]
            }
        </div>
        
        <div class="col-lg-3 col-sm-6">
        {* Логин *}
        {component 'form' 
            template    = 'text' 
            name        = "login"
            placeholder = $aLang.auth.registration.form.fields.login.placeholder
            type        = "text"
            validate    = [ 
                entity  => $oUserProfile,
                remote  => true
            ]}
        </div>
        
        <div class="col-lg-3 col-sm-6">
        {* Пароль *}
        {component 'form' template='text' 
            type        = "password"
            name        = "password"
            placeholder = $aLang.auth.registration.form.fields.password.placeholder
            validate    = [ 
                entity  => $oUserProfile
            ]}
        </div>
    </div>
    </div>
        
    <div class="d-flex flex-wrap justify-content-start justify-content-sm-end align-items-center pt-2">
        {if Config::Get('module.user.captcha_use_registration')}
            {component "form.recaptcha" 
                validate    = [ 
                    entity  => $oUserProfile
                ]
                classesGroup     = "mt-sm-0 mt-2 mb-0"
                name        = "recaptcha"}
        {/if}



        {hook run='form_registration_end'}

        {if $redirectUrl}
            <input type="hidden"  class="ls-field-input is-valid" value="{$redirectUrl}" name="return-path" >        
        {/if}

        <input type="hidden"  class="ls-field-input is-valid" value="company" name="role" >

        {component 'button' 
            classes = "mt-sm-0 mt-2"
            name='submit_register' 
            type="submit" 
            bmods='primary' 
            text=$aLang.auth.registration.form.fields.submit.text}
    </div>
</form>