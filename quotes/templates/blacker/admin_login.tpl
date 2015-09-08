			<form action="./" name="admin" method="POST">
                <div class="form-control">
                    <label for="username">Username</label>
                    <div class="input-group">
                        <input type="text" name="username" id="username" />
                    </div>
                </div>
                <div class="form-control">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" />
                    </div>
                </div>
                <div class="form-control">
                    <div class="input-group">
                        <input type="hidden" name="do" value="login" />
                        <input type="submit" value="Login" />
                    </div>
                </div>
            </form>
