			<h2>Add Quote</h2>
			<p>Enter your quote below.</p>
			<form action="./" name="admin" method="POST">
				<div class="form-control">
					<label for="quote">Quote</label>
					<div class="input-group">
						<textarea id="quote" name="quote" cols="80" rows="5"></textarea>
					</div>
				</div>
				<div class="form-control">
					<div class="input-group">
						<input type="hidden" name="do" value="add" />
						<input type="submit" value="Submit" />
					</div>
				</div>
			</form>
