# Contributing

Thank you for your interest in contributing to this repository. Before submitting any pull requests, please be aware of the following house rules.

## House rules

1. This project uses the [git-flow][1] discipline.
   1. **Do not** submit pull requests against the *main* branch!
   2. **Do** submit pull requests against the *develop* branch!
2. If this project has a version, it uses [semantic versioning][2].

## First steps for developers

When you first check out the repository, set up the QI git hooks and git flow.

1. `git config --local core.hooksPath .githooks/`
2. `git flow init -d`

## PHP debugging in VS Code

Create `.vscode/launch.json`:

```json
{
    // Use IntelliSense to learn about possible attributes.
    // Hover to view descriptions of existing attributes.
    // For more information, visit: https://go.microsoft.com/fwlink/?linkid=830387
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Xdebug",
            "type": "php",
            "request": "launch",
            "port": 9000,
        }
    ]
}
```

You may have to adjust the port number based on your PHP configuration.

The configuration above assumes the following in `php.ini`:

```ini
[xdebug]
xdebug.mode = develop,debug
xdebug.client_port = 9000
xdebug.idekey = VSCODE
xdebug.start_with_request = default
xdebug.output_dir = "/tmp"
xdebug.trigger_value = ""
```

You can debug on the CLI by setting the `XDEBUG_TRIGGER` environment variable: `XDEBUG_TRIGGER=1 sake dev/build`, for example.

[1]: https://nvie.com/posts/a-successful-git-branching-model/
[2]: http://semver.org
