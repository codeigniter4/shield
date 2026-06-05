from datetime import datetime

def on_config(config, **kwargs):
    config.copyright = config.copyright.format(year=datetime.now().year)
    return config
