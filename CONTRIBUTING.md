# Genisys Contribution Guidelines

### Questions

No questions on GitHub. Consider one of our [chats](https://github.com/iTXTech/Genisys#discussion).  
Channel rule: **Don't ask to ask, if anyone is here or alive, or uses something. Just ask, and we'll get moving along. Thanks.**

### Issues

- **No questions.** See above.
- **Follow the template** and provide the information we ask for.
- Use English properly, or ask someone to examine your words.
- When posting a feature request, try to describe as much as you can, and don't make it too broad.
- Avoid generic titles like "Crash", "Help" or "Broken". Do your best as long as it fits in the line.
- Avoid unneeded emoji reactions, unless you're participating and providing informations.

### Code Contributions

- **Avoid using GitHub Web Editor.** GitHub Web doesn't provide every Git feature, and using the web editor means you haven't tested the code. It's immediately obvious if you've used the Web editor, and if you do, your PR is likely to be rejected. Also **do not use a mobile device** for code editing.

- **No copy-pasted contents.** Not only license issues exist, you're also ignoring what the author intended to do. Blindly copied content are strongly discouraged. (We recommend the original author to open a PR)

- **Do not consider spamming pull requests.** If you don't know how to squash and force push, use [this site](https://github.com/edx/edx-platform/wiki/How-to-Rebase-a-Pull-Request) to learn how.

- Test your changes before opening a pull request. **Do not submit a PR if the CI fails.**

- Make sure you can fully explain WHY and HOW your changes work. If you can't provide a full and comprehensive explanation as to why your changes work and what the effects are, do not submit a pull request.

- Use descriptive commit messages. See [an example](http://tbaggery.com/2008/04/19/a-note-about-git-commit-messages.html) here.

- One change per commit. Squash redundant commits.

- Pull requests doing little things like bumping protocol numbers will be closed as spam. We don't need people spamming us with protocol numbers, especially when said people do not TEST things properly before making a PR. There is always a reason why the protocol VERSION NUMBER is changed - to reflect internal BACKWARDS-INCOMPATIBLE changes.
