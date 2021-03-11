function pageAdditionSubmitCheck()
{
    const uri = document.getElementById('llb-page-add-uri')
    const passwords = document.getElementById('llb-page-add-passwords')
    const button = document.getElementById('llb-page-add-submit')

    if (uri.value != '' &&
        passwords.value != '')
    {
        if (button.hasAttribute('disabled')) button.removeAttribute('disabled')
    }
    else if (!button.hasAttribute('disabled')) button.setAttribute('disabled', 'true')
}
