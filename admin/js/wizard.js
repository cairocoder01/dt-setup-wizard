function sendApiRequest(endpoint, data, basePath) {
  const formData = new FormData();
  buildFormData(formData, data);
  return fetch(`/wp-json/${basePath ?? 'dt-core/v1'}${endpoint}`, {
    method: 'POST',
    body: formData,
    headers: {
      "X-WP-Nonce": window.wpApiSettings.nonce,
    }
  }).then((response) => response.json());
}
function buildFormData (formData, data, parentKey) {
  if (data && typeof data === 'object' && !(data instanceof Date) && !(data instanceof File)) {
    Object.keys(data).forEach(key => {
      buildFormData(formData, data[key], parentKey ? `${parentKey}[${key}]` : key);
    });
  } else {
    const value = data == null ? '' : data;
    formData.append(parentKey, value);
  }
};
function onInstallClick(event) {
  if (event){
    event.preventDefault();
  }
  var formData = new FormData(event.target, event.submitter);
  console.log(event.submitter);
  //event.target.parentNode.removeChild(event.target);
  //var buttons = document.getElementsByTagName('button');
  var button = formData.get('button');
  var nonce = formData.get('ajax_nonce');

  if (button !== 'all'){
    installPlugin(button, nonce);
  } else {
    try {
      formData.entries()
      .filter((entry) => entry[0] !== 'button' && entry[0] !== 'ajax_nonce')
      .forEach((plugin, i) => {
        setTimeout(() => {
          installPlugin(plugin[1], nonce);
        }, i * 500);
      });
    } catch (error) {
      console.error(error);
      showMessage('Error setting option', 'error');
    }
  }
}
function installPlugin(plugin, nonce) {
  console.log(plugin);
  console.log(nonce);
  try {
    plugin = JSON.parse(plugin);
  } catch (error) {
    showMessage(plugin, 'error')
  }
  if (plugin['url'] == null) {
    installBySlug(plugin, nonce);
  } else {
    installByUrl(plugin['url']);
  }
}
function installBySlug(plugin, nonce) {
  const data = {
    slug: plugin['slug'],
    action: 'install-plugin',
    _ajax_nonce: nonce
  }
  const slugFormData = new FormData();
  buildFormData(slugFormData, data);
  console.log('installing: ' + plugin['slug']);
  showMessage(`Installing: ${plugin['slug']}`);
  return fetch(`/wp-admin/admin-ajax.php`, {
    method: 'POST',
    body: slugFormData,
    headers: {
      "X-WP-Nonce": nonce,
    }
  }).catch((err) => {
    console.log('Error:');
    console.log(err);
  }).then(() => {
    setTimeout(() => { activate(plugin['slug']) }, 500);
    //activate(plugin['slug']);
  });
}
function installByUrl(pluginUrl) {
  const isDtPlugin = pluginUrl.includes('http') || pluginUrl.includes('/');
  const slug = isDtPlugin
    ? pluginUrl.split('/')[4]
    : pluginUrl;
  console.log('installing: ' + slug);
  showMessage(`Installing: ${slug}`);
  if (isDtPlugin) {
    sendApiRequest('/plugin-install', { download_url: pluginUrl }, 'dt-admin-settings')
      .then((success) => {
        if (success) {
          console.log('Successfully installed ' + slug);
          showMessage(`Installed ${slug}`, 'success');
          setTimeout(() => { activate(slug) }, 500);
          //activate(slug);
        } else {
          throw new Exception('Error');
        }
      })
      .catch((error) => {
        console.error('Error installing plugin.', error);
        showMessage(`Error installing plugin ${pluginUrl}`, 'error');
      });
  } else {
    const body = new FormData();
    body.append('slug', slug);
    body.append('status', 'active');
    // console.log(window.wpApiSettings);
    const url = `${window.wpApiSettings.root}${window.wpApiSettings.versionString}plugins`;
    fetch(url, {
      method: 'POST',
      body,
      headers: {
        "X-WP-Nonce": window.wpApiSettings.nonce,
      }
    }).then((response) => response.json())
      .then((success) => {
        console.log('Successfully installed ' + slug, success);
        const sp1 = document.createElement("span");
        sp1.textContent = "Active";
        document.getElementById(slug).replaceWith(sp1);
        document.getElementById(slug+"hidden").remove();
        showMessage(`Installed and Activated ${slug}`, 'success');
      })
      .catch((error) => {
        console.error('Error installing plugin.', error);
        showMessage(`Error installing plugin ${pluginUrl}`, 'error');
      });
  }
}
function activate(pluginSlug) {
  console.log('activating: ' + pluginSlug);
  showMessage(`Activating: ${pluginSlug}`);
  sendApiRequest('/plugin-activate', { plugin_slug: pluginSlug }, 'dt-admin-settings')
    .then(function(success) {
      if (success) {
        console.log('Successfully activated ' + pluginSlug);
        const sp1 = document.createElement("span");
        sp1.textContent = "Active";
        document.getElementById(pluginSlug).replaceWith(sp1);
        document.getElementById(pluginSlug+"hidden").remove();
        showMessage(`Activated ${pluginSlug}`, 'success');
      } else {
        throw new Exception('Error');
      }
    })
    .catch((error) => {
      console.error('Error activating plugin.', error);
      showMessage(`Error activating plugin ${pluginSlug}`, 'error');
    });
}
function createUser(user) {
  console.log('creating user: ', user);
  showMessage(`Creating user: ${user.username}`);
  sendApiRequest('/user', user, 'disciple-tools-setup-wizard/v1')
    .then((data) => {
      if (data && Number.isInteger(data)) {
        console.log('Created user', data);
        showMessage(`Created user: ${user.username} (#${data})`, 'success');
        const sp1 = document.createElement("span");
        sp1.textContent = "Done!";
        document.getElementById(user.username).replaceWith(sp1);
        document.getElementById(user.username+"hidden").remove();
      } else {
        throw data;
      }
    })
    .catch((error) => {
      console.error('Error creating user', error);
      showMessage(`Error creating user: ${user.username}`, 'error');
    });
}
function onClickUserButton(event) {
  if (event){
    event.preventDefault();
  }
  var formData = new FormData(event.target, event.submitter);
  const button = formData.get('button');
  console.log(formData.get(button));
  if (button !== 'all') {
    try {
      createUser(
        parseOptionValue(formData.get(button))
      );
    } catch (error) {
      console.error(error);
      showMessage('Error creating user', 'error');
    }
  } else {
    try {
      console.log(formData.entries());
      formData.entries()
      .filter((entry) => entry[0] !== 'button')
      .forEach((user) => createUser(JSON.parse(user[1])));
    } catch (error) {
      console.error(error);
      showMessage('Error creating user', 'error');
    }
  }
}
function setOption(option) {
  console.log('setting option: ', option);
  showMessage(`Setting option: ${option.key}`);
  sendApiRequest('/option', option, 'disciple-tools-setup-wizard/v1')
    .then((data) => {
      console.log('Set option', data);
      showMessage(`Set option: ${option.key}`, 'success');
      const sp1 = document.createElement("span");
      sp1.textContent = "Done!";
      document.getElementById(option.key).replaceWith(sp1);
      document.getElementById(option.key+"input").remove();
      const sp2 = document.createElement("span");
      sp2.textContent = option.value;
      document.getElementById(option.key+"value").replaceWith(sp2);
    })
    .catch((error) => {
      console.error('Error setting option', error);
      showMessage(`Error setting option: ${option.key}`, 'error');
    });
}
function parseOptionValue(value) {
  if (value.charAt(0) == '{') {
    return JSON.parse(value);
  } else {
    return value;
  }
}
function onClickOptionButton(event) {
  if (event){
    event.preventDefault();
  }
  var formData = new FormData(event.target, event.submitter);
  const button = formData.get('button');
  if (button !== 'all') {
    try {
      setOption(
        {
          "key":button,
          "value":parseOptionValue(formData.get(button))
        }
      );
    } catch (error) {
      console.error(error);
      showMessage('Error setting option', 'error');
    }
  } else {
    try {
      console.log(formData.entries());
      formData.entries()
      .filter((entry) => entry[0] !== 'button')
      .map((entry) => ({
        key: entry[0],
        value: entry[1],
      }))
      .forEach((option) => setOption(option));
    } catch (error) {
      console.error(error);
      showMessage('Error setting option', 'error');
    }
  }
}
function onClickManualButton(event) {
  if (event){
    event.preventDefault();
  }
  var formData = new FormData(event.target, event.submitter);
  const button = formData.get('button');
  console.log(button);
  var option = {
    "key": 'dt_manual_steps',
    "value":parseOptionValue(button)
  }
  showMessage(`Setting option: ${option.key}`);
  sendApiRequest('/option', option, 'disciple-tools-setup-wizard/v1')
  .then((data) => {
    console.log('Set option', data);
    showMessage(`Set option: ${option.key}`, 'success');
  })
  .catch((error) => {
    console.error('Error setting option', error);
    showMessage(`Error setting option: ${option.key}`, 'error');
  });
}
function advancedConfigSubmit(evt) {
    if (evt) {
      evt.preventDefault();
    }
    const formData = new FormData(evt.target);
    const configRaw = formData.get('config');
    if (!configRaw) {
      console.error('Config value is required');
      showMessage('Config value is required', 'error');
      return;
    }
    try {
      // console.log(configRaw);
      const config = JSON.parse(configRaw);
      processConfig(config);
    } catch (error) {
      console.error(error);
      showMessage('Could not parse config JSON', 'error');
    }
}
function processConfig(config) {
  // console.log('Processing: ', config);
  if (config.plugins) {
    for (plugin of config.plugins) {
      install(plugin);
    }
  }
  if (config.users) {
    for (user of config.users) {
      createUser(user);
    }
  }
  if (config.options) {
    for (option of config.options) {
      setOption(option);
    }
  }
}
function settingsConfigSubmit(evt) {
  if (evt) {
    evt.preventDefault();
  }
  const formData = new FormData(evt.target);
  const configRaw = formData.get('config');
  if (!configRaw) {
    console.error('Config value is required');
    showMessage('Config value is required', 'error');
    return;
  }
  try {
    const config = JSON.parse(configRaw);
    saveConfig(config);
  } catch (error) {
    console.error(error);
    showMessage('Could not parse config JSON', 'error');
  }
}
function saveConfig(config) {
  var option = {
          "key": "dt_setup_wizard_config",
          "value": config
      }
  setOption(option);
  var manualOptions = {};
    for (step of config.steps) {
      if ( !step.config ) {
        manualOptions[step.name] = false;
      }
    }
    var manualOption = {
      "key": "dt_manual_steps",
      "value": manualOptions
    }
    sendApiRequest('/option', manualOption, 'disciple-tools-setup-wizard/v1')
    .then((data) => {
      console.log('Set option', data);
    })
    .catch((error) => {
      console.error('Error setting option', error);
    });
}
function createMessageLi(content, context) {
  const message = document.createElement('li');
  message.innerHTML = content;
  if (context) {
    message.classList.add(context);
  }
  return message;
}
function showMessage(content, context) {
  const msgContainer = document.getElementById('message-container');
  const message = createMessageLi(content, context);
  msgContainer.append(message);
  const logContainer = document.getElementById('log-container');
  const log = createMessageLi(content, context);
  const logs = logContainer.getElementsByClassName('logs');
  if (logs && logs.length) {
    logs[0].append(log);
    logs[0].scrollTo(0, logs[0].scrollHeight);
  }
  setTimeout(function () {
    message.remove();
  }, 6500);
}
function toggleLogContainer(evt) {
  const container = document.getElementById('log-container');
  if (container) {
    container.classList.toggle('expand');
  }
  const dashicons = (evt.currentTarget || evt.target).getElementsByClassName('dashicons');
  if (dashicons) {
    for (dashicon of dashicons) {
      dashicon.classList.toggle('dashicons-arrow-down-alt2');
      dashicon.classList.toggle('dashicons-arrow-up-alt2');
    }
  }
}
function onExpandableTextareaInput({ target:elm }){
  // make sure the input event originated from a textarea and it's desired to be auto-expandable
  if( !elm.classList.contains('auto-expand') || !elm.nodeName == 'TEXTAREA' ) return
  if (elm.scrollHeight > elm.offsetHeight) {
    elm.style.minHeight = `calc(${elm.scrollHeight}px + 2rem)`;
  }
}
// global delegated event listener
document.addEventListener('input', onExpandableTextareaInput)