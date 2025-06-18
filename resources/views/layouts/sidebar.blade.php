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
					@php
						$showChartOfCode = false;

						if(auth()->user()->hasRole('Super Admin')){
							$showChartOfCode = true;
						}

						if(auth()->user()->hasRole('Admin')){
							$showChartOfCode = true;
						}
					@endphp

					@if($showChartOfCode)
					
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
							@if(auth()->user()->hasRole('Super Admin'))
								<ul aria-expanded="false">
									<li><a href="{{ route('roles.index') }}">Role</a></li>
								</ul>
								<ul aria-expanded="false">
									<li><a href="{{ route('permissions.assign') }}">Assign Permission</a></li>
								</ul>
								<ul aria-expanded="false">
									<li><a href="{{ route('institutes.index') }}">Institute</a></li>
								</ul>
								<ul aria-expanded="false">
									<li><a href="{{ route('admin.users.index') }}">Admin</a></li>
								</ul>
							@endif	

							@if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Admin'))
							<ul aria-expanded="false">
								<li><a href="{{ route('admin.courses.index') }}">Courses</a></li>
							</ul>
							<ul aria-expanded="false">
								<li><a href="{{ route('sessions.index') }}">Session</a></li>
							</ul>
							<ul aria-expanded="false">
								<li><a href="{{ route('classes.index') }}">Classes</a></li>
							</ul>
							<ul aria-expanded="false">
								<li><a href="{{ route('sections.index') }}">Section</a></li>
							</ul>
							<ul aria-expanded="false">
								<li><a href="{{ route('admin.teachers.index') }}">Teacher</a></li>
							</ul>
							<ul aria-expanded="false">
								<li><a href="{{ route('students.index') }}">Students</a></li>
							</ul>
							<ul aria-expanded="false">
							  <li><a href="{{ route('class-slots.index') }}">Time Slot</a></li>
							</ul>

						  @endif
						</ul>
					</li>
					@endif
					@if(auth()->user()->hasAnyRole(['Super Admin', 'Admin','Teacher']))
					<li>
						<a href="admin-dashboard.html" class="has-arrow" aria-expanded="false">
							<div class="menu-icon">
								<svg width="24" height="24" viewBox="0 0 640 512" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path fill="#ffffff" d="M320 32c-17.7 0-32 14.3-32 32v8.2L97.1 143.8c-15.4 4.6-15.4 26.7 0 31.2l62.7 18.8c-12.3 15.2-19.8 34.4-19.8 55.3v42.1c0 10.5 10.1 18.1 20.2 15.1L320 240l160.9 66.3c10.1 4.2 20.2-4.6 20.2-15.1v-42.1c0-20.9-7.5-40.1-19.8-55.3l62.7-18.8c15.4-4.6 15.4-26.7 0-31.2L352 72.2V64c0-17.7-14.3-32-32-32zm160 352h-32.1c-29.6 0-56.6 16.3-70.1 42.3L352 512H512l-32-85.3c-13.5-26-40.5-42.3-70-42.3zM320 352a96 96 0 1 0 0-192 96 96 0 1 0 0 192z"/>
								</svg>
							</div>
							<span class="nav-text">Enrollments</span>
						</a>
						<ul aria-expanded="false">
							 <li><a href="{{ route('enrollments.index') }}">Student Enrollment</a></li>
							 
							 <li><a href="{{ route('teacher.enrollments.form') }}">Teacher Enrollment</a></li>
						</ul>
					</li>
					@endif

					<li>
						<a href="admin-dashboard.html" class="has-arrow" aria-expanded="false">
							<div class="menu-icon">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 640 512">
									<path fill="#ffffff" d="M622.34 153.46 346.44 49.06c-22.13-8.35-46.85-8.35-68.98 0L17.56 153.46c-21.18 8.01-21.18 
									38.06 0 46.07l58.3 22.04v71.91c0 35.35 57.31 64 128 64s128-28.65 128-64v-48.31l184 69.6V416h-56c-13.25 
									0-24 10.75-24 24v48c0 13.25 10.75 24 24 24h160c13.25 0 24-10.75 24-24v-48c0-13.25-10.75-24-24-24h-56v-82.7l58.3-22.04c21.18-8.01 
									21.18-38.06 0-46.07z"/>
								</svg>
							</div>
							<span class="nav-text">Class Management</span>
						</a>
						
						<ul aria-expanded="false">
							@if(auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Teacher', 'Student']))
							<li><a href="{{ route('lectures.index') }}">Lecture</a></li>
							@endif

							@if(auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Teacher']))
							<li><a href="{{ route('attendances.index') }}">Mark Attedance</a></li>
							<li><a href="{{ route('results.index') }}">Result Uplaod</a></li>
							@endif

							@if(auth()->user()->hasAnyRole(['Super Admin', 'Admin']))
							<li><a href="{{ route('time-table.index') }}">Time table</a></li>
							@endif
						</ul>						
						
					</li>
					@if(auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Teacher']))
						<li>
							<a href="admin-dashboard.html" class="has-arrow" aria-expanded="false">
								<div class="menu-icon">
									<svg width="24" height="24" viewBox="0 0 512 512" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path fill="#ffffff" d="M64 64C64 46.33 78.33 32 96 32H416C433.7 32 448 46.33 448 64V448C448 465.7 433.7 480 416 480H96C78.33 480 64 465.7 64 448V64zM144 128V384H192V128H144zM240 224V384H288V224H240zM336 176V384H384V176H336z"/>
									</svg>
								</div>
								<span class="nav-text">Reports</span>
							</a>
							
							<ul aria-expanded="false">
								
								<li><a href="{{ route('enrollments.report') }}">Enrollment Report</a></li>
								<li><a href="{{ route('attendances.report') }}">Attedance Report</a></li>
								<li><a href="{{ route('results.view') }}">Result Report</a></li>
							</ul>						
							
						</li>
					@endif
				</ul>
			</div>
        </div>
		
        <!--**********************************
            Sidebar end
        ***********************************-->