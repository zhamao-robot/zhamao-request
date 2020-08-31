# 代码测试
问题：在 Swoole 的协程环境下才能运行的代码不能直接使用 PHPUnit 进行单元测试。

解决：使用此文件夹内的 `phpunit` 文件作为 PHPUnit 可执行文件进行测试：
```bash
./phpunit ZMRequestTest.php
```

如果使用 IDE（如 PhpStorm），请将 `Test Frameworks` 中 PHPUnit 的配置 `Path to phpunit.phar` 改为此 `phpunit` 文件即可。
