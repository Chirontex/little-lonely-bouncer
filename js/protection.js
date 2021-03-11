function protectionFormSubmitCheck()
{
    const email = document.getElementById('llb-protection-email')
    const button = document.getElementById('llb-protection-submit')

    if (email.value != '')
    {
        if (button.hasAttribute('disabled')) button.removeAttribute('disabled')
    }
    else if (!button.hasAttribute('disabled')) button.setAttribute('disabled', 'true')
}
