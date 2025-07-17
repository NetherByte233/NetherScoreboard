# NetherScoreboard

A simple, customizable scoreboard plugin for PocketMine-MP.

## Features
- Shows player money (purse), online count, ping, and more
- Fully customizable scoreboard lines and title via `config.yml`
- Supports normal, abbreviated, or mixed purse display
- Easy to install and configure

## Installation
1. Place the `NetherScoreboard` folder in your `plugins` directory.
2. Start your server. The plugin will generate a default `config.yml` in `plugins/NetherScoreboard/resources/`.
3. Edit `config.yml` to customize the scoreboard.

## Screenshot
<p align="center">
  <img src="https://github.com/NetherByte233/images/blob/main/FullScreen.jpg?raw=true" width="80%" />
</p>

## Configuration
Example `config.yml`:
```yaml
scoreboard:
  title: "§l§bNether§eScoreboard"
  lines:
    - "§aPlayer: §f{name}"
    - "§aMoney: §6{money}"
    - "§aOnline: §e{online}/{max_online}"
    - "§aPing: §d{ping}"
    - "§7----------------"
    - "§eNetherByte"
    - "§eTo"
    - "§eSubscribe"
update-interval: 20
purse-format: abbreviated # 'normal', 'abbreviated', or 'mixed' (mixed = normal up to 100 million, abbreviated above)

# Some variables you can also use:
#   {server_ip}
#   {server_port}
```

## Placeholders
- `{name}`: Player name
- `{money}`: Player's purse (from NetherEconomy)
- `{online}`: Online players
- `{max_online}`: Max players
- `{ping}`: Player ping
- `{server_ip}`: Server bind IP (may show 0.0.0.0)
- `{server_port}`: Server port

## Purse Display Modes
- `normal`: Always shows the full number (e.g. 123456789)
- `abbreviated`: Always shows the abbreviated form (e.g. 123M, 1.2B)
- `mixed`: Shows the full number up to 100 million, then abbreviated for larger values

## Notes
- For best results, use a short scoreboard title and lines (Bedrock has display limits).
- If you want to show your public server IP or name, hardcode it in the config lines.
- The plugin is designed for PocketMine-MP API 5.0.0+.
- Use `{money}` only if you are using NetherEconomy plugin as your economy plugin

---

Enjoy your custom scoreboard! For help or suggestions, open an issue or contact the author. 
