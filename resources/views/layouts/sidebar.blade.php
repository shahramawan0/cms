 <!--**********************************
            Sidebar start
        ***********************************-->
		<div class="deznav">
            <div class="deznav-scroll">
				<ul class="metismenu" id="menu">
					<li class="menu-title fs-5">{{ Auth::user()->institute->name ?? 'YOUR COMPANY' }}</li>
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
					<li>
						<a href="admin-dashboard.html" class="has-arrow" aria-expanded="false">
							<div class="menu-icon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M4 19.5C4 18.6716 4.67157 18 5.5 18H20.5V4H5.5C4.67157 4 4 4.67157 4 5.5V19.5Z" stroke="#90959F" stroke-width="2"/>
									<path d="M4 19.5C4 20.3284 4.67157 21 5.5 21H20.5V18H5.5C4.67157 18 4 18.6716 4 19.5Z" stroke="#90959F" stroke-width="2"/>
								</svg>
							</div>
							<span class="nav-text">Course</span>
						</a>
						<ul aria-expanded="false">
							<li><a href="{{ route('admin.courses.index') }}">Courses List</a></li>
						</ul>
					</li>

					<li>
						<a href="admin-dashboard.html" class="has-arrow" aria-expanded="false">
							<div class="menu-icon">
								<svg width="24" height="24" viewBox="0 0 640 512" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path fill="#ffffff" d="M320 32c-17.7 0-32 14.3-32 32v8.2L97.1 143.8c-15.4 4.6-15.4 26.7 0 31.2l62.7 18.8c-12.3 15.2-19.8 34.4-19.8 55.3v42.1c0 10.5 10.1 18.1 20.2 15.1L320 240l160.9 66.3c10.1 4.2 20.2-4.6 20.2-15.1v-42.1c0-20.9-7.5-40.1-19.8-55.3l62.7-18.8c15.4-4.6 15.4-26.7 0-31.2L352 72.2V64c0-17.7-14.3-32-32-32zm160 352h-32.1c-29.6 0-56.6 16.3-70.1 42.3L352 512H512l-32-85.3c-13.5-26-40.5-42.3-70-42.3zM320 352a96 96 0 1 0 0-192 96 96 0 1 0 0 192z"/>
								</svg>
							</div>
							<span class="nav-text">Student</span>
						</a>
						<ul aria-expanded="false">
							<li><a href="{{ route('students.index') }}">student List</a></li>
							<li><a href="{{ route('enrollments.index') }}">student Enrollment</a></li>

						</ul>
					</li>
					<li>
						<a href="admin-dashboard.html" class="has-arrow" aria-expanded="false">
							<div class="menu-icon">
								<svg width="24" height="24" viewBox="0 0 448 512" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path fill="#ffffff" d="M152 64c0-8.8-7.2-16-16-16s-16 7.2-16 16v32H64V64c0-8.8-7.2-16-16-16s-16 7.2-16 16v32H0v48h448V96h-32V64c0-8.8-7.2-16-16-16s-16 7.2-16 16v32h-56V64c0-8.8-7.2-16-16-16s-16 7.2-16 16v32h-80V64zM0 192v272c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V192H0zm64 64h64v64H64v-64zm128 0h64v64h-64v-64zm128 0h64v64h-64v-64zM64 384h64v64H64v-64zm128 0h64v64h-64v-64zm128 0h64v64h-64v-64z"/>
								</svg>
							</div>
							<span class="nav-text">Session</span>
						</a>
						<ul aria-expanded="false">
							<li><a href="{{ route('sessions.index') }}">Session List</a></li>
							
						</ul>
					</li>
					<li>
						<a href="admin-dashboard.html" class="has-arrow" aria-expanded="false">
							<div class="menu-icon">
								<svg width="24" height="24" viewBox="0 0 640 512" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path fill="#ffffff" d="M0 64C0 28.7 28.7 0 64 0h512c35.3 0 64 28.7 64 64v256c0 17.7-14.3 32-32 32H336v32h80c8.8 0 16 7.2 16 16v32h56c13.3 0 24 10.7 24 24v16H128v-16c0-13.3 10.7-24 24-24h56v-32c0-8.8 7.2-16 16-16h80v-32H32c-17.7 0-32-14.3-32-32V64zm96 224h448V96H96v192z"/>
								</svg>
							</div>
							<span class="nav-text">Classes</span>
						</a>
						<ul aria-expanded="false">
							<li><a href="{{ route('classes.index') }}">Class List</a></li>
						</ul>
					</li>
					<li>
						<a href="admin-dashboard.html" class="has-arrow" aria-expanded="false">
							<div class="menu-icon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path fill="#ffffff" d="M3 3h7v7H3V3zm0 11h7v7H3v-7zm11-11h7v7h-7V3zm0 11h7v7h-7v-7z"/>
								</svg>
							</div>
							<span class="nav-text">Section</span>
						</a>
						<ul aria-expanded="false">
							<li><a href="{{ route('sections.index') }}">Section List</a></li>
						</ul>
					</li>
					

					
					
				</ul>
			</div>
        </div>
		
        <!--**********************************
            Sidebar end
        ***********************************-->