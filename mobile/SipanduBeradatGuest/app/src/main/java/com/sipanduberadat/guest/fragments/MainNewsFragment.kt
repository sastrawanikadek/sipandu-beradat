package com.sipanduberadat.guest.fragments

import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.LinearLayout
import androidx.core.content.ContextCompat
import androidx.core.view.get
import androidx.fragment.app.Fragment
import androidx.lifecycle.ViewModelProvider
import androidx.viewpager.widget.ViewPager
import com.google.android.flexbox.FlexDirection
import com.google.android.flexbox.FlexWrap
import com.google.android.flexbox.FlexboxLayout
import com.google.android.flexbox.FlexboxLayoutManager
import com.google.android.material.progressindicator.LinearProgressIndicator
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.adapters.NewsAdapter
import com.sipanduberadat.guest.adapters.NewsHeadlineCoverViewPagerAdapter
import com.sipanduberadat.guest.adapters.NewsHeadlineTitleViewPagerAdapter
import com.sipanduberadat.guest.models.BeritaWrapper
import com.sipanduberadat.guest.utils.getViewport
import com.sipanduberadat.guest.utils.toPx
import com.sipanduberadat.guest.viewModels.MainViewModel
import kotlinx.android.synthetic.main.layout_main_news.view.*

class MainNewsFragment: Fragment() {
    private lateinit var viewModel: MainViewModel
    private var handler: Handler? = null
    private var runnable: Runnable? = null
    private val headline: MutableList<BeritaWrapper> = mutableListOf()
    private val news: MutableList<BeritaWrapper> = mutableListOf()
    private val headlineMaxSlide: Int = 7
    private var previousPage = 0
    private var currentPage = 0
    private var currentProgress = 0
    private var slideOrientation = "forward"

    private fun onInitNews() {
        if (view != null && viewModel.reports.value != null && viewModel.guestReports.value != null &&
                viewModel.news.value != null && viewModel.accommodationNews.value != null &&
                viewModel.blockedRoads.value != null) {
            view!!.shimmer_container.stopShimmer()
            view!!.shimmer_container.visibility = View.GONE
            view!!.content_container.visibility = View.VISIBLE
            headline.clear()
            news.clear()

            viewModel.blockedRoads.value!!.map { headline.add(BeritaWrapper(null,
                    null, it, null, null)) }
            viewModel.accommodationNews.value!!.map {
                if (headline.size < headlineMaxSlide) headline.add(BeritaWrapper(null, it,
                        null, null, null))
                else news.add(BeritaWrapper(null, it, null, null,
                        null))
            }
            viewModel.news.value!!.map {
                if (headline.size < headlineMaxSlide) headline.add(BeritaWrapper(it,
                        null, null, null, null))
                else news.add(BeritaWrapper(it, null, null,
                        null, null))
            }
            viewModel.reports.value!!.filter { it.status == 1 }.map {
                news.add(BeritaWrapper(null, null, null, it,
                        null)) }
            viewModel.guestReports.value!!.filter { it.status == 1 }.map {
                news.add(BeritaWrapper(null, null, null,
                        null, it)) }
            headline.sortByDescending {
                when {
                    it.blockedRoad != null -> it.blockedRoad!!.start_time.time
                    it.news != null -> it.news!!.time.time
                    else -> it.accommodationNews!!.time.time
                }
            }
            news.sortByDescending {
                when {
                    it.news != null -> it.news!!.time.time
                    it.accommodationNews != null -> it.accommodationNews!!.time.time
                    it.report != null -> it.report!!.time.time
                    else -> it.guestReport!!.time.time
                }
            }

            view!!.headline_container.visibility = if (headline.isEmpty()) View.GONE else View.VISIBLE
            view!!.empty_container.visibility = if (news.isNotEmpty()) View.GONE else View.VISIBLE
            view!!.title_view_pager.adapter = NewsHeadlineTitleViewPagerAdapter(view!!.context, headline)
            view!!.cover_view_pager.adapter = NewsHeadlineCoverViewPagerAdapter(view!!.context, headline)
            view!!.recycler_view.adapter!!.notifyDataSetChanged()
            view!!.indicator_container.removeAllViews()

            Handler(Looper.getMainLooper()).postDelayed({view!!.title_view_pager.requestLayout()},
                100)
            view!!.title_view_pager.currentItem = currentPage
            view!!.cover_view_pager.currentItem = currentPage

            if (handler != null && runnable != null) {
                handler!!.removeCallbacks(runnable!!)
                handler!!.postDelayed(runnable!!, 500)
            }

            for (i in 0 until headline.size) {
                val progressBar = LinearProgressIndicator(view!!.context)
                progressBar.layoutParams = FlexboxLayout.LayoutParams(FlexboxLayout.LayoutParams.WRAP_CONTENT,
                        FlexboxLayout.LayoutParams.WRAP_CONTENT).apply {
                    flexGrow = 1.0f
                    marginStart = if (i == 0) 0 else (8).toPx()
                    marginEnd = if (i == headline.size - 1) 0 else (8).toPx() }
                progressBar.trackColor = ContextCompat.getColor(view!!.context, R.color.transparent_white)
                progressBar.trackCornerRadius = (8).toPx()
                view!!.indicator_container.addView(progressBar)
            }
        }
    }

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View? {
        val view = inflater.inflate(R.layout.layout_main_news, container, false)
        handler = Handler(Looper.getMainLooper())
        runnable = object: Runnable {
            override fun run() {
                if (view.indicator_container.childCount > 0) {
                    currentProgress += (5000 / 500)

                    if (headline.size > 1) {
                        (view.indicator_container[currentPage] as
                                LinearProgressIndicator).rotation = if (slideOrientation == "forward")
                            0f else 180f
                        (view.indicator_container[currentPage] as LinearProgressIndicator)
                                .setProgressCompat(currentProgress, true)
                    }

                    if (currentProgress >= 100) {
                        currentProgress = 0
                        previousPage = currentPage
                        currentPage += if (slideOrientation == "forward") 1 else -1
                        view.title_view_pager.currentItem = currentPage
                        view.cover_view_pager.currentItem = currentPage
                    }
                }
                if (handler != null) {
                    handler!!.removeCallbacks(this)
                    handler!!.postDelayed(this, 500)
                }
            }
        }
        handler!!.postDelayed(runnable!!, 500)

        viewModel = ViewModelProvider(activity!!).get(MainViewModel::class.java)

        view.headline_container.layoutParams = LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT,
                (0.4 * getViewport(activity!!).heightPixels).toInt())
        view.title_view_pager.apply {
            clipToPadding = false
            setPadding((16).toPx(), 0, (16).toPx(), (2).toPx())
            pageMargin = (16).toPx()
            adapter = NewsHeadlineTitleViewPagerAdapter(view.context, headline)
            addOnPageChangeListener(object: ViewPager.OnPageChangeListener {
                override fun onPageScrolled(position: Int, positionOffset: Float, positionOffsetPixels: Int) {}

                override fun onPageSelected(position: Int) {
                    currentProgress = 0
                    previousPage = if (currentPage == position) previousPage else currentPage
                    currentPage = position
                    currentItem = currentPage
                    view.cover_view_pager.currentItem = currentPage
                    if (view.indicator_container.childCount > previousPage) {
                        (view.indicator_container[currentPage] as
                                LinearProgressIndicator).rotation = if (slideOrientation == "forward")
                            0f else 180f
                        (view.indicator_container[previousPage] as LinearProgressIndicator)
                                .setProgressCompat(0, true)
                    }

                    if (currentPage == headline.size - 1) {
                        slideOrientation = "backward"
                    } else if (currentPage == 0) {
                        slideOrientation = "forward"
                    }

                    if (handler != null && runnable != null) {
                        handler!!.removeCallbacks(runnable!!)
                        handler!!.postDelayed(runnable!!, 500)
                    }
                }

                override fun onPageScrollStateChanged(state: Int) {}
            })
            currentItem = 0
        }
        view.cover_view_pager.apply {
            setPageTransformer(false) { page, _ ->
                page.alpha = 0.1f
                page.visibility = View.VISIBLE
                page.animate().alpha(1f).duration = 300
            }
            adapter = NewsHeadlineCoverViewPagerAdapter(view.context, headline)
            addOnPageChangeListener(object: ViewPager.OnPageChangeListener {
                override fun onPageScrolled(position: Int, positionOffset: Float, positionOffsetPixels: Int) {}

                override fun onPageSelected(position: Int) {
                    currentProgress = 0
                    previousPage = if (currentPage == position) previousPage else currentPage
                    currentPage = position
                    currentItem = currentPage
                    view.title_view_pager.currentItem = currentPage
                    if (view.indicator_container.childCount > previousPage) {
                        (view.indicator_container[currentPage] as
                                LinearProgressIndicator).rotation = if (slideOrientation == "forward")
                            0f else 180f
                        (view.indicator_container[previousPage] as LinearProgressIndicator)
                                .setProgressCompat(0, true)
                    }

                    if (currentPage == headline.size - 1) {
                        slideOrientation = "backward"
                    } else if (currentPage == 0) {
                        slideOrientation = "forward"
                    }

                    if (handler != null && runnable != null) {
                        handler!!.removeCallbacks(runnable!!)
                        handler!!.postDelayed(runnable!!, 500)
                    }
                }

                override fun onPageScrollStateChanged(state: Int) {}
            })
            currentItem = 0
        }
        view.recycler_view.apply {
            layoutManager = FlexboxLayoutManager(view.context, FlexDirection.ROW,
                    FlexWrap.WRAP)
            adapter = NewsAdapter(view.context, news)
        }

        viewModel.reports.observe(activity!!, { if (it != null) onInitNews() })
        viewModel.guestReports.observe(activity!!, { if (it != null) onInitNews() })
        viewModel.news.observe(activity!!, { if (it != null) onInitNews() })
        viewModel.accommodationNews.observe(activity!!, { if (it != null) onInitNews() })
        viewModel.blockedRoads.observe(activity!!, { if (it != null) onInitNews() })

        view.swipe_refresh.setOnRefreshListener {
            Handler(Looper.getMainLooper()).postDelayed({
                view.swipe_refresh.isRefreshing = false
                view.content_container.visibility = View.GONE
                view.shimmer_container.visibility = View.VISIBLE
                view.shimmer_container.startShimmer()
                if (handler != null && runnable != null) handler!!.removeCallbacks(runnable!!)
                viewModel.reports.value = null
                viewModel.guestReports.value = null
                viewModel.news.value = null
                viewModel.accommodationNews.value = null
                viewModel.blockedRoads.value = null
            }, 300)
        }

        return view
    }

    override fun onResume() {
        super.onResume()
        if (handler == null) {
            handler = Handler(Looper.getMainLooper())
            handler!!.postDelayed(runnable!!, 500)
        }
    }

    override fun onPause() {
        super.onPause()
        handler = null
    }
}