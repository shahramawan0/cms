 <!--**********************************
            Sidebar start
        ***********************************-->
		<div class="deznav">
            <div class="deznav-scroll">
				<ul class="metismenu" id="menu">
					<li class="menu-title">YOUR COMPANY</li>
					<li><a class="" href="{{ route('dashboard') }}" aria-expanded="false">
						
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
							
						</ul>
					</li>
					<li>
						<a href="task.html" class="has-arrow" aria-expanded="false">
							<div class="menu-icon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M12 2L3 4V9C3 15.5 12 22 12 22C12 22 21 15.5 21 9V4L12 2ZM15.5 9.2L17.2 11L12 16.5L7.2 11L8.8 9.2L12 12.3L15.5 9.2Z" fill="#90959F"/>
								</svg>
							</div>
							<span class="nav-text">Permission</span>
						</a>
						<ul aria-expanded="false">
							<li><a href="{{ route('permissions.assign') }}">Assign Permission</a></li>
						</ul>
					</li>
					<li>
						<a href="task.html" class="has-arrow" aria-expanded="false">
							<div class="menu-icon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M12 2L2 7L12 12L22 7L12 2Z" fill="#90959F"/>
									<path d="M4 10V18H8V10H4ZM10 10V18H14V10H10ZM16 10V18H20V10H16Z" fill="#90959F"/>
									<path d="M2 20H22V22H2V20Z" fill="#90959F"/>
								</svg>
							</div>
							<span class="nav-text">Institute</span>
						</a>
						<ul aria-expanded="false">
							<li><a href="{{ route('institutes.index') }}">Institute List</a></li>
						</ul>
					</li>
					<li>
						<a href="admin-dashboard.html" class="has-arrow" aria-expanded="false">
							<div class="menu-icon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M12 2C10.067 2 8.5 3.567 8.5 5.5C8.5 7.433 10.067 9 12 9C13.933 9 15.5 7.433 15.5 5.5C15.5 3.567 13.933 2 12 2ZM12 11C9.33 11 4 12.34 4 15V18H20V15C20 12.34 14.67 11 12 11Z" fill="#90959F"/>
									<path d="M17.5 2L21 3.5V8.5L17.5 10L14 8.5V3.5L17.5 2Z" fill="#90959F"/>
								</svg>
							</div>
							<span class="nav-text">Admin User</span>
						</a>
						<ul aria-expanded="false">
							<li><a href="{{ route('admin.users.index') }}">Admin List</a></li>
						</ul>
					</li>
					<li>
						<a href="admin-dashboard.html" class="has-arrow" aria-expanded="false">
							<div class="menu-icon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M12 2C10.067 2 8.5 3.567 8.5 5.5C8.5 7.433 10.067 9 12 9C13.933 9 15.5 7.433 15.5 5.5C15.5 3.567 13.933 2 12 2ZM12 11C9.33 11 4 12.34 4 15V18H20V15C20 12.34 14.67 11 12 11Z" fill="#90959F"/>
									<path d="M17.5 2L21 3.5V8.5L17.5 10L14 8.5V3.5L17.5 2Z" fill="#90959F"/>
								</svg>
							</div>
							<span class="nav-text">Teacher</span>
						</a>
						<ul aria-expanded="false">
							<li><a href="{{ route('admin.teachers.index') }}">Teacher List</a></li>
						</ul>
					</li>
					
					
				</ul>
			</div>
        </div>
		
        <!--**********************************
            Sidebar end
        ***********************************-->