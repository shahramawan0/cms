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
					{{-- <li class="menu-title">OUR Modules</li> --}}
					<li><a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
						<div class="menu-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<g opacity="0.5">
								  <path d="M4 20H6V10H4V20Z" fill="white"/>
								  <path opacity="0.4" d="M9 20H11V4H9V20Z" fill="white"/>
								  <path opacity="0.4" d="M14 20H16V13H14V20Z" fill="white"/>
								  <path d="M19 20H21V7H19V20Z" fill="white"/>
								</g>
							  </svg>
							  

						</div>
						<span class="nav-text">Chart Of Code</span>
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
							<li><a href="{{ route('teacher.enrollments.index') }}">Enroll List</a></li>
						</ul>						
						
					</li>
				</ul>
			</div>
        </div>
		
        <!--**********************************
            Sidebar end
        ***********************************-->