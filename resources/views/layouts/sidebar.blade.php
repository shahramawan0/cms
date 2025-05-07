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
					<li class="menu-title">OUR Modules</li>
					<li><a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
						<div class="menu-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<g opacity="0.5">
								<path d="M9.34933 14.8577C5.38553 14.8577 2 15.47 2 17.9174C2 20.3666 5.364 21 9.34933 21C13.3131 21 16.6987 20.3877 16.6987 17.9404C16.6987 15.4911 13.3347 14.8577 9.34933 14.8577Z" fill="white"/>
								<path opacity="0.4" d="M9.34935 12.5248C12.049 12.5248 14.2124 10.4062 14.2124 7.76241C14.2124 5.11865 12.049 3 9.34935 3C6.65072 3 4.48633 5.11865 4.48633 7.76241C4.48633 10.4062 6.65072 12.5248 9.34935 12.5248Z" fill="white"/>
								<path opacity="0.4" d="M16.1734 7.84876C16.1734 9.19508 15.7605 10.4513 15.0364 11.4948C14.9611 11.6022 15.0276 11.7468 15.1587 11.7698C15.3407 11.7996 15.5276 11.8178 15.7184 11.8216C17.6167 11.8705 19.3202 10.6736 19.7908 8.87119C20.4885 6.19677 18.4415 3.79544 15.8339 3.79544C15.5511 3.79544 15.2801 3.82419 15.0159 3.87689C14.9797 3.88456 14.9405 3.9018 14.921 3.93247C14.8955 3.97176 14.9141 4.02254 14.9395 4.05608C15.7233 5.13217 16.1734 6.44208 16.1734 7.84876Z" fill="white"/>
								<path d="M21.7791 15.1693C21.4318 14.444 20.5932 13.9466 19.3173 13.7023C18.7155 13.5586 17.0854 13.3545 15.5697 13.3832C15.5472 13.3861 15.5345 13.4014 15.5325 13.411C15.5296 13.4263 15.5365 13.4493 15.5658 13.4656C16.2664 13.8048 18.9738 15.2805 18.6333 18.3928C18.6187 18.5289 18.7292 18.6439 18.8672 18.6247C19.5335 18.5318 21.2478 18.1705 21.7791 17.0475C22.0737 16.4534 22.0737 15.7634 21.7791 15.1693Z" fill="white"/>
								</g>
							</svg>

						</div>
						<span class="nav-text">Modules</span>
						</a>
						<ul aria-expanded="false">
							
							<li><a class="has-arrow" href="javascript:void(0);" aria-expanded="false">Role<span class="badge badge-danger badge-xs ms-1">NEW</span></a>
								<ul aria-expanded="false">
									<li><a href="{{ route('roles.index') }}">Add Role</a></li>
								
								</ul>
							</li>
							<li><a class="has-arrow" href="javascript:void(0);" aria-expanded="false">Permission</a>
								<ul aria-expanded="false">
									<li><a href="{{ route('permissions.assign') }}">Assign Permission</a></li>
									
								</ul>
							</li>
							<li><a class="has-arrow" href="javascript:void(0);" aria-expanded="false">Institute</a>
								<ul aria-expanded="false">
									<li><a href="{{ route('institutes.index') }}">Institute List</a></li>
									
								</ul>
							</li>
							
							<li><a class="has-arrow" href="javascript:void(0);" aria-expanded="false">Admin User</a>
								<ul aria-expanded="false">
									<li><a href="{{ route('admin.users.index') }}">Admin List</a></li>
								</ul>
							</li>
							<li><a class="has-arrow" href="javascript:void(0);" aria-expanded="false">Teacher</a>
								<ul aria-expanded="false">
									<li><a href="{{ route('admin.teachers.index') }}">Teacher List</a></li>
								</ul>
							</li>
							<li><a class="has-arrow" href="javascript:void(0);" aria-expanded="false">Course</a>
								<ul aria-expanded="false">
									<li><a href="{{ route('admin.courses.index') }}">Courses List</a></li>
								</ul>
							</li>
							<li><a class="has-arrow" href="javascript:void(0);" aria-expanded="false">Session</a>
								<ul aria-expanded="false">
									<li><a href="{{ route('sessions.index') }}">Session List</a></li>
								</ul>
							</li>
							<li><a class="has-arrow" href="javascript:void(0);" aria-expanded="false">Classes</a>
								<ul aria-expanded="false">
									<li><a href="{{ route('classes.index') }}">Class List</a></li>
								</ul>
							</li>
							<li><a class="has-arrow" href="javascript:void(0);" aria-expanded="false">Section</a>
								<ul aria-expanded="false">
									<li><a href="{{ route('sections.index') }}">Section List</a></li>
								</ul>
							</li>
						</a>
						
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
								<svg width="24" height="24" viewBox="0 0 640 512" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path fill="#ffffff" d="M320 32c-17.7 0-32 14.3-32 32v8.2L97.1 143.8c-15.4 4.6-15.4 26.7 0 31.2l62.7 18.8c-12.3 15.2-19.8 34.4-19.8 55.3v42.1c0 10.5 10.1 18.1 20.2 15.1L320 240l160.9 66.3c10.1 4.2 20.2-4.6 20.2-15.1v-42.1c0-20.9-7.5-40.1-19.8-55.3l62.7-18.8c15.4-4.6 15.4-26.7 0-31.2L352 72.2V64c0-17.7-14.3-32-32-32zm160 352h-32.1c-29.6 0-56.6 16.3-70.1 42.3L352 512H512l-32-85.3c-13.5-26-40.5-42.3-70-42.3zM320 352a96 96 0 1 0 0-192 96 96 0 1 0 0 192z"/>
								</svg>
							</div>
							<span class="nav-text">Course Enrollment</span>
						</a>
						<ul aria-expanded="false">
							<li><a href="{{ url('/EnrollCourse') }}">Enroll List</a></li>
						</ul>
						
					</li>
				</ul>
			</div>
        </div>
		
        <!--**********************************
            Sidebar end
        ***********************************-->