{**
 * Форма входа
 *
 * @param string $redirectUrl
 *}


{hook run='login_begin'}
<form data-url="{router page='auth/ajax-login'}" method="post" name="register_user" data-form-ajax class=" mt-3" novalidate>

    {* Логин или Email*}
    {component 'form' 
        template    = 'text' 
        name        = "mail_login"
        placeholder = $aLang.auth.login.form.fields.login_or_email.placeholder
        type        = "text"
        }

    {* Пароль *}
    {component 'form' template='text' 
        type        = "password"
        name        = "password"
        placeholder = $aLang.auth.login.form.fields.password.placeholder
        }

    {* Запомнить *}
    {component 'form' template='checkbox'
        classes = "is-valid"
        name    = 'remember'
        label   = $aLang.auth.login.form.fields.remember.label
        checked = true}

    {hook run='form_login_end'}

    {if $redirectUrl}
        <input type="hidden"  class="ls-field-input is-valid" value="{$redirectUrl}" name="return-path" >      
    {/if}

    <div class="d-flex justify-content-center">
        {component 'button' 
            classes = ""
            name='submit_login' 
            type="submit" 
            bmods='primary' 
            text=$aLang.auth.login.form.fields.submit.text}
    </div>
</form>
{hook run='login_end'}

{component "auth.social"}