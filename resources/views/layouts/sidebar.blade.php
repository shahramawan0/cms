 <!--**********************************
            Sidebar start
        ***********************************-->
		<div class="deznav">
            <div class="deznav-scroll">
				<ul class="metismenu" id="menu">
					<li class="menu-title">YOUR COMPANY</li>
					<li><a class="" href="javascript:void(0);" aria-expanded="false">
						
						<span class="nav-text">Dashboard</span>
						</a>
						
					</li>
					{{-- <li class="menu-title">Modules</li> --}}
					<li>
						<a href="task.html" class="has-arrow" aria-expanded="false">
							<div class="menu-icon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M12 2C10.3431 2 9 3.34315 9 5C9 6.65685 10.3431 8 12 8C13.6569 8 15 6.65685 15 5C15 3.34315 13.6569 2 12 2Z" fill="#90959F"/>
									<path d="M4 20C4 16.6863 7.13401 14 11 14H13C16.866 14 20 16.6863 20 20V21H4V20Z" fill="#90959F"/>
								</svg>
							</div>	
							<span class="nav-text">Role</span>
						</a>
						<ul aria-expanded="false">
							<li><a href="{{ route('roles.index') }}">Add Role</a></li>
							<li><a href="{{ route('permissions.assign') }}">Assign Permission</a></li>
						</ul>
					</li>
				</ul>
			</div>
        </div>
		
        <!--**********************************
            Sidebar end
        ***********************************-->