# Rack Panel WordPress Plugin

This plugin is used to control AOE Greens control panels via WordPress.

We have provided this code to you in the hopes of making sustainable food production a world-wide reality. For more information on our open source software and how to reach us, please visit [https://aoegreens.com/about/](https://aoegreens.com/about/).

## Usage

This code is primarily intended for use by shortcodes embedded in custom post types.

For example, `[aoe_current_device_status]` or `[aoe_toggle_current_device]`.

Please be aware that this code is intended to be run in a cloud environment with access to network-connected micro controllers. For more information on how this code can be put into production, see our other repos:
* [Raspberry Pi Hardware Control RESTful API Server](https://github.com/aoegreens/srv_pi_controller)
* [Request Forwarding Server](https://github.com/aoegreens/srv_forward_internal_api)

## Building

This code is intended to be built with [EBBS](https://github.com/eons-dev/bin_ebbs) and published to the [eons Infrastructure Repository](https://infrastructure.tech).

To build this code locally, you can run:
```bash
pip install ebbs
ebbs
```
Make sure that you are in the root of this repository, where the `build.json` file lives.