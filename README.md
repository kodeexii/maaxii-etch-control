# MaaXII Etch Control

MaaXII Etch Control is a universal remote management and programmatic page builder plugin for WordPress and Etch. It allows AI agents and remote clients to manage site content and structure using a generic JSON blueprint system, fully aligned with the Etch AI workflow.

- **Version**: 1.0.0
- **Author**: MaaXII Solutions and Services
- **License**: GPL-2.0-or-later

## Features

- **Universal Etch Builder**: Build fully editable Etch pages programmatically using JSON blueprints.
- **Section Append**: Add new sections to the end of existing Etch pages without overwriting current content.
- **Auto-Style Registration**: Automatically registers any new CSS classes found in your blueprints into the Etch Style Manager.
- **Smart Inspection**: Retrieve current block structures for remote editing or auditing.
- **Housekeeping**: Remotely delete pages using keyword search.
- **BEM-First Architecture**: Encourages clean, modular CSS naming conventions out of the box.
- **Automatic Layout Detection**: Maps system layout styles (`section`, `container`) automatically based on applied classes.
- **Abilities API Native**: Built specifically for the WordPress Abilities API and Model Context Protocol (MCP).

## Requirements

Before installing, ensure your environment meets the following criteria:
- **PHP**: 8.2 or higher.
- **WordPress**: 6.0 or higher.
- **Etch Page Builder**: Active (Required for block rendering).
- **WordPress Abilities API**: Active (Required for ability registration).
- **MCP Adapter**: Required (To expose abilities to AI via Model Context Protocol).
- **Automatic CSS (ACSS)**: Recommended (Required for blueprints using ACSS variables).

## AI & Remote Client Requirements

To effectively use this plugin, the AI Agent or Remote Client should have:
- **JSON Schema Awareness**: Ability to understand and generate complex JSON structures based on input schemas.
- **MCP/Abilities Support**: A bridge to discover and execute WordPress Abilities.
- **Contextual Knowledge**: Awareness of Etch Page Builder's hierarchy (Section > Container) and BEM/ACSS conventions.
- **Serialization Handling**: Capability to send structured "Blueprint JSON" rather than raw HTML.

## Abilities API

This plugin registers the following primary abilities:

### `maaxii/build-etch-page`
Builds or updates an Etch page based on a JSON layout schema.
- **Inputs**: `title` (string), `layout` (array), `styles` (object, optional).

### `maaxii/append-etch-section`
Adds a new section to the end of an existing Etch page.
- **Inputs**: `title` (string), `blocks` (array), `styles` (object, optional).

### `maaxii/get-page-blocks`
Retrieves the current block structure of a page.

### `maaxii/delete-pages`
Remotely delete pages using keyword search.

### `maaxii/ping`
Simple connectivity check.

## Examples

You can find example Blueprint JSON files in the `examples/` directory:
- `bersihwp-blueprint.json`: A full service landing page example.
- `binawp-blueprint.json`: A reference structure based on the BinaWP page.

## References

- **Etch Page Builder**: [Documentation](https://docs.etchwp.com/) - The engine powering the visual development.
- **Automatic CSS (ACSS)**: [Documentation](https://docs.automaticcss.com/) - The utility framework used for styling variables.
- **BEM Methodology**: [Get BEM](http://getbem.com/) - The naming convention used for CSS classes.
- **WordPress Abilities API**: [Abilities API Guide](https://github.com/google/gemini-cli) - Understanding the execution layer.

## Installation

1. Copy the `maaxii-etch-control` folder to `/wp-content/plugins/`.
2. Activate the plugin in the WordPress Dashboard.
3. Ensure the **WordPress Abilities API** plugin (or core feature) is active.

## License

This project is licensed under the GPL-2.0-or-later License - see the [LICENSE](LICENSE) file for details.
